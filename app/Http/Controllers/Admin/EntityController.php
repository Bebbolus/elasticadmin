<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\EntityNotDeletedException;
use App\Helper\FormInputCreator;
use App\Models\GenericEntity;
use App\Exceptions\EntityNotFoundException;
use Elasticsearch\ClientBuilder;
use Elasticsearch\Common\Exceptions\Curl\CouldNotConnectToHost;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Elasticsearch\Common\Exceptions\NoNodesAvailableException;
use Illuminate\Support\Facades\Validator;

class EntityController extends Controller
{
    private $client;

    public function __construct()
    {
        $this->client = ClientBuilder::create()->setHosts(config('elasticquent.config.hosts', 'localhost'))->build();
    }

    public function index()
    {
        $filters = [];
         try {
             if (request()->has('filters[index]')) {
                 $filters['index'] = request()->get('filters[index]');
             } else {
                 $filters['index'] = '';
                 $index = $this->client->indices()->getMapping();
             }

             return view('admin.manage_entity.global_search', [
                 'indices' => $index,
                 'filters' => $filters,
                 'results' => [],
                 'hits' => 0,
                 'pagehit' => 0,
                 'tableHead' => '',
                 'type' => ''
             ]);
         }catch(NoNodesAvailableException $e){
            return redirect()->to('/')->with('success_message', 'No ElasticSearch Nodes Available');
         }
    }

    // TODO Fatta da fare paginazione
    public function postIndex()
    {
        $request = request()->except('_token');

        $validator = Validator::make($request,
            [
                'filters.index' => 'required',
                'filters.type' => 'required',
            ]);

        if ($validator->fails()) {
            return back()->with('error_message', 'Spiacenti, dovete selezionare almeno un indice ed una tipologia sulla quale effettuare l\'operazione');
        }

        $index = trim($request['filters']['index']);
        $type = trim($request['filters']['type']);


        if ((!request()->has('id')) AND (request()->has('operation') AND request()->get('operation') !== "create")) {

            $page =1;
            if(request()->has('pagehit')) $page = (request()->input('pagehit') / (config('elasticquent.max_result','20')));


            $model = new GenericEntity($type, $index);

            $input = ['metadata'=>[]];
            if(request()->has('metadata'))$input = request()->only('metadata');


            try {

                $purgedInput = array_map('array_filter', $input);

                $results = $model->get($purgedInput['metadata'], [], $page);
            } catch (CouldNotConnectToHost $e) {
                return back()->with('error_message', 'Spiacenti, il servizio non è disponibile');
            } catch (EntityNotFoundException $e) {
                return back()->with('error_message', 'Spiacenti, nessun risultato trovato');
            }


            $finalResults = [];
            $element = [];
            $tableHead = ['id'];

            foreach ($results['hits']['hits'] as $result) {
                $finalResult = $result['_source'];
                foreach ($finalResult as $key => $item) {


                    $element[$key] = $item;
                    if (!in_array($key, $tableHead) and !is_array($item) and !(strpos($key, 'id'))) $tableHead[] = $key;

                }
                $element['id'] = $result['_id'];
                $finalResults[] = $element;
            }



            return view('admin.manage_entity.global_search', [
                'indices' => $indices = $this->client->indices()->getMapping(),
                'filters' => $request['filters'],
                'results' => $finalResults,
                'hits' => $results['hits']['total'],
                'pagehit' => $page,
                'tableHead' => $tableHead,
                'type' => $type
            ]);

        } elseif (request()->has('operation') AND request()->get('operation') !== "create") {

            if (request()->has('id')) {
                if (request()->has('operation') AND request()->get('operation') === "edit") {
                    return redirect()->to('/entity/edit/' . $index . '/' . $type . '/' . trim(request()->get('id')));
                }
                if (request()->has('operation') AND request()->get('operation') === "delete") {
                    return redirect()->to('/entity/delete/' . $index . '/' . $type . '/' . trim(request()->get('id')));
                } else  return redirect()->to('/entity/show/' . $index . '/' . $type . '/' . trim(request()->get('id')));
            }
        } else {
            return redirect()->to('/entity/create/' . $index . '/' . $type);
        }
    }

    // TODO Fatta
    public function showFromId($index, $type, $id)
    {
        try {
            $model = new GenericEntity($type, $index);
            $results = $model->find($id);
        } catch (CouldNotConnectToHost $e) {
            return back()->with('error_message', 'Spiacenti, il servizio non è disponibile');
        } catch (EntityNotFoundException $e) {
            return back()->with('error_message', 'Spiacenti, nessun risultato trovato');
        }

        dd($results);

    }

    // TODO fatta : da perfezionare
    public function getCreate($index, $type)
    {
        $helper = new FormInputCreator();
        $formBody = $helper->buildForm($this->getStandardFormData($index, $type));
        return view('admin.manage_entity.global_create', compact(
            'formBody',
            'index',
            'type'
        ));

    }

    // TODO da fare
    public function postCreate($index, $type)
    {


        dd(request()->all());


//      $type = strtolower(trim($type));
//     $typeField = strtoupper($type);

        $params = ['index' => $index, 'type' => $type];
        $result = $this->client->indices()->getMapping($params);
        $field = $result[$index]['mappings'][$type]['properties'];

        //validazione input
        $validationRules = [];
        foreach ($field as $item) {
            $validationRules[$item[0]] = $item[2];
        }

        $validator = \Validator::make(request()->all(), $validationRules);
        if ($validator->fails()) {
            $messages = $validator->errors();
            $msg = '';
            foreach ($messages->all() as $message) {
                $msg .= ' ' . $message;
            }
            return redirect()->back()
                ->with('error_message', $msg)
                ->withInput();
        }

        $input = request()->except('_token');
        foreach ($input as $k => $v) {
            if ($v == '') {
                unset($input[$k]);
            } elseif ((str_contains($k, '_DATE') or str_contains($k, '_CHECKED')) and $v != '') {
                $d = trim($input[$k]);
                $dmy = preg_split("[/]", $d);
                $input[$k] = strtotime($dmy[1] . "/" . $dmy[0] . "/" . $dmy[2]);
            }
        }

        $data = [];
        foreach ($field as $element) {
            $data[$element[0]] = '';
        }

        $data = $this->getParentNames($data);


        foreach ($input as $k => $v) {
            foreach ($data as $a) {
                if ((str_contains($k, $a)) and $v != '') {
                    if (str_contains($k, '_CODE')) {
                        // ottengo il nome camelcase
                        $className = ucfirst(camel_case(strtolower($a)));
                        // richiamo l'istanza tramite app();
                        $newParentRepository = app('App\\Repositories\\' . $className . '\\' . $className . 'ElasticSearchRepository');
                        $newParentData = $newParentRepository->getIdFromAttribute([$k => $v]);
                        unset($input[$k]);
                        $input[$a . '_ID'] = $newParentData;
                    }
                }
            }
        }

        // ottengo il nome camelcase
        $className = ucfirst(camel_case(strtolower($type)));
        // richiamo l'istanza tramite app();
        $parentRepository = app('App\\Repositories\\' . $className . '\\' . $className . 'ElasticSearchRepository');

        $result = $parentRepository->create($input);
        return redirect()->to('/admin/entity/show/' . $type . '/' . $result)->with('success_message', 'elemento creato con successo');
    }

    // TODO fatta : da perfezionare
    public function getEdit($index, $type, $id)
    {

        try {
            $model = new GenericEntity($type, $index);
            $data = $model->find($id);
        } catch (CouldNotConnectToHost $e) {
            return back()->with('error_message', 'Spiacenti, il servizio non è disponibile');
        } catch (EntityNotFoundException $e) {
            return back()->with('error_message', 'Spiacenti, nessun risultato trovato');
        }

        $fixedData = [];

        $data = $data['_source'];

        foreach ($data as $k => $v) {

            if ($k != 'id') {
                if ($k == 'updated_at' or $k == 'created_at' or strpos($k, 'date') == TRUE or strpos($k, 'DATE') == TRUE) {
                    $fixedData[$k] = date('d-m-Y', $v);
                } else {
                    $fixedData[$k] = $v;
                }
            }
        }


        #merge array standard needed camp (standardFormData) and specific object data ($data)
        $standardFormData = $this->getStandardFormData($index, $type);

        foreach ($standardFormData as $k => $standardElement) {
            if (isset($data[$standardElement[2]])) {
                if (is_array($fixedData[$standardElement[2]])) {
                    $standardElement[0] = 'select';
                    foreach ($fixedData[$standardElement[2]] as $option) {
                        array_push($standardElement[4], $option );
                    }
                    $standardFormData[$k] = $standardElement;
                } else {
                    $standardElement[3] = $fixedData[$standardElement[2]];
                    $standardFormData[$k] = $standardElement;
                }
            }
        }

        $helper = new FormInputCreator();
        $formBody = $helper->buildForm($standardFormData);
        return view('admin.manage_entity.global_edit', compact(
            'formBody',
            'fixedData',
            'index',
            'type',
            'id'
        ));
    }

    // TODO da fare
    public function postUpdate($index, $type, $id)
    {

        //validazione input
        $validationRules = [];
        foreach ($field as $item) {
            $validationRules[$item[0]] = $item[2];
        }
        $validator = \Validator::make(request()->all(), $validationRules);
        if ($validator->fails()) {
            $messages = $validator->errors();
            $msg = '';
            foreach ($messages->all() as $message) {
                $msg .= ' ' . $message;
            }
            return redirect()->back()
                ->with('error_message', $msg)
                ->withInput();
        }

        $input = $request->except('_token');

        foreach ($input as $k => $v) {
            if (str_contains($k, '_DATE') or str_contains($k, '_CHECKED')) {
                $d = trim($input[$k]);
                if ($d != '') {
                    $dmy = preg_split("[/]", $d);
                    $input[$k] = strtotime($dmy[1] . "/" . $dmy[0] . "/" . $dmy[2]);
                }
            }

        }


        // ottengo il nome camelcase
        $className = ucfirst(camel_case(strtolower($type)));
        // richiamo l'istanza tramite app();
        $parentRepository = app('App\\Repositories\\' . $className . '\\' . $className . 'ElasticSearchRepository');
        $parentData = $parentRepository->getFromId($id);
        $data = $this->getParentNames($parentData);
//        dd($input);

        foreach ($input as $k => $v) {
            foreach ($data as $a) {

                //SOSTITUISCO TUTTI I DATI DEI PARENT CON L'ID DEL PARENT
                if (str_contains($k, $a)) {
                    if (str_contains($k, '_CODE')) {
                        // ottengo il nome camelcase
                        $className = ucfirst(camel_case(strtolower($a)));
                        // richiamo l'istanza tramite app();
                        $newParentRepository = app('App\\Repositories\\' . $className . '\\' . $className . 'ElasticSearchRepository');
                        $newParentData = $newParentRepository->getIdFromAttribute([$k => $v]);
                        unset($input[$k]);
                        $input[$a . '_ID'] = $newParentData;
                    }
                    //SE L'INPUT CONTIENE UN ID ESPLICITO DI UN PARENT, ELIMINO TUTTI I FIGLI DELLO STESSO
                    if (str_contains($k, '_ID')) {
                        // ottengo il nome camelcase
                        $className = ucfirst(camel_case(strtolower($a)));
                        // richiamo l'istanza tramite app();
                        $newParentRepository = app('App\\Repositories\\' . $className . '\\' . $className . 'ElasticSearchRepository');
                        $newParentData = $newParentRepository->getFromId($v);

                        if ($k == 'TOWN_CITY_ID') {
                            foreach ($newParentData as $itmk => $itmv) {
                                if (str_contains($itmk, '_ID') and $itmk != 'EDM_PARENTS_ID' and $itmk != 'TOWN_CITY_ID') {
                                    if (array_key_exists($itmk, $input)) {
                                        unset($input[$itmk]);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        $input['ID'] = $id;
        try {
            $result = $parentRepository->update($input);
        } catch (EntityNotFoundException $e) {
            Session::flash('error_message', (string)$e->getMessage());
            return redirect()->back();
        }
        return redirect()->to('/admin/entity/show/' . $type . '/' . $id)->with('success_message', 'elemento con ID = ' . $result . ' modificato con successo');
    }

    // TODO Fatta
    public function delete($index, $type, $id)
    {
        $type = trim($type);

        $model = new GenericEntity($type, $index);

        try {
            $model->deleteEntity($id);
        } catch (EntityNotFoundException $e) {
            return redirect()->back()->with('error_message', 'Tipo non trovato');
        } catch (EntityNotDeletedException $e) {
            return redirect()->back()->with('error_message', 'Impossibile cancellare il tipo');
        }

        return redirect()->back()->with('success_message', 'Tipo Cancellato');
    }

    // TODO da ottimizzare e perfezionare
    public function getStandardFormData($index, $type)
    {

        $params = ['index' => $index, 'type' => $type];
        $result = $this->client->indices()->getMapping($params);
        $field = $result[$index]['mappings'][$type]['properties'];

        $formData = [];


        foreach ($field as $key => $value) {

            $translation = ucwords(str_replace('_', ' ', $key));

            if ($key != 'id' and $key != 'doc') {
                if ($value['type'] == 'date' or $key == 'updated_at' or $key == 'created_at' or strpos($key, 'date') == TRUE or strpos($key, 'DATE') == TRUE) {
                    $formData[] = ["date", $translation, $key, '', [], ""];
                } elseif ($value['type'] == 'select') {
                    $formData[] = ["select", $translation, $key, '', [], ""];
                } else {
                    $formData[] = ["text", $translation, $key, '', [], ""];
                }
            }
        }

        return $formData;

    }

}
