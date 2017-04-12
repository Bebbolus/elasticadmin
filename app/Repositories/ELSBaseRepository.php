<?php

namespace App\Repositories;

use App\Exceptions\EntityNotCreatedException;
use App\Exceptions\EntityNotFoundException;
use App\Exceptions\EntityNotUpdatedException;
use App\Exceptions\MoreEntityWithSameAttributeException;
use App\Exceptions\NoOperationNeededException;
use Elasticsearch\ClientBuilder;
use Elasticsearch\Common\Exceptions\Missing404Exception;


class ELSBaseRepository
{
    use ELSQueryBuilderTrait;

    private $index;
    private $type;
    private $client;
    private $maxResultsSize;
    private $isModel;
    protected $model;
    protected $uniqueKey;

    public function __construct($model, $type, $index = '', $uniqueKey = 'code', $isModel = true)
    {
        if ($index == '') $this->index = config('elasticquent.default_index','index');
        else $this->index = $index;
        $this->type = $type;
        $this->maxResultsSize = config('elasticquent.max_result',1000000);
        $this->client = $client = ClientBuilder::create()->setHosts(config('elasticquent.config.hosts','localhost'))->build();
        $this->model = $model;
        $this->uniqueKey = $uniqueKey;
        $this->isModel = $isModel;
    }



    public function find($id)
    {
        $params = [
            'index' => $this->index,
            'type' => $this->type,
            'id' => $id
        ];
        try{
            if($this->isModel)return $this->getClassInstance($this->client->get($params));
            else return $this->client->get($params);
        }catch (Missing404Exception $e){
            throw new EntityNotFoundException();
        }

        /*
         * return Exception to manage:
         * NoNodesAvailableException = server non raggiungibile
         */
    }


    public function search($term, $page = 0)
    {
        $params = [
            'index' => $this->index,
            'type'  => $this->type,
            'size'  => $this->maxResultsSize,
            'from'  => $page * $this->maxResultsSize,
            '_source_exclude' => 'PAG*'
        ];
        $params['body']['query']['match']['_all'] = $term;

        if($this->isModel)return $this->model->hydrateElasticsearchResult($this->client->search($params));
        else return $this->client->search($params);


    }

    public function findByKey($keyValue)
    {
        $results = $this->get([$this->uniqueKey=>$keyValue]);

        if($results->count()>1){
            throw new MoreEntityWithSameAttributeException();
        }
        elseif($results->count() == 1){
            return ($results->first());
        }
        else{
            throw new EntityNotFoundException();
        }

    }


    /**
     * Search from condition function
     *
     * @param array $conditions
     * @param array $requiredField Es. ['DOCUMENT_SIZE', 'DOCUMENT_CODE', 'SITE_CODE', 'SITE_NAME', 'TOWN_CITY_NAME'];
     * @param int $page
     * @return mixed
     * @throws \Exception
     */
    public function get($conditions = [], $requiredField = [], $page = 0)
    {

        $searchParams['index'] = $this->index;
        $searchParams['type'] = $this->type;
        $searchParams['size'] = $this->maxResultsSize;
        $searchParams['from'] = $page * $this->maxResultsSize;
        $searchParams['body']['query']['bool']['must'] = [];
        $searchParams['_source_exclude']= ['PAG*'];

        $searchParams['body']['highlight']['order'] = 'score';
        $searchParams['body']['highlight']['fields'] = ['PAGE_*' => ['number_of_fragments' => 3], 'PAG_*' => ['number_of_fragments' => 3]];
        $searchParams['body']['highlight']['pre_tags'] = ['<strong>'];
        $searchParams['body']['highlight']['post_tags'] = ['</strong>'];

        foreach ($requiredField as $field) {
            $searchParams['body']['query']['bool']['must'][]['exists'] = ['field' => $field];
        }

        $filter = $this->makeFilterCondition($conditions);

        if(!empty($filter))$searchParams['body']['query']['bool']['must'][] = $filter;

        if($this->isModel)return $this->model->hydrateElasticsearchResult($this->client->search($searchParams));
        else return $this->client->search($searchParams);

    }

    public function getId($conditions = [], $requiredField = [])
    {

        $searchParams['index'] = $this->index;
        $searchParams['type'] = $this->type;
        $searchParams['size'] = 1;
        $searchParams['body']['query']['bool']['must'] = [];
        $searchParams['_source']= false;

        foreach ($requiredField as $field) {
            $searchParams['body']['query']['bool']['must'][]['exists'] = ['field' => $field];
        }

        $filter = $this->makeFilterCondition($conditions);

        $searchParams['body']['query']['bool']['must'][] = $filter;


        $result = $this->client->search($searchParams);

        if($result['hits']['total']>1){
            throw new MoreEntityWithSameAttributeException();
        }
        elseif($result['hits']['total'] == 1){
            return $result['hits']['hits'][0]['_id'];

        }
        else {

            throw new EntityNotFoundException();
        }

    }

    public function count($conditions = [], $requiredField = [])
    {

        $searchParams['index'] = $this->index;
        $searchParams['type'] = $this->type;
        $searchParams['size'] = 0;
        $searchParams['body']['query']['bool']['must'] = [];
        $searchParams['_source']= false;

        foreach ($requiredField as $field) {
            $searchParams['body']['query']['bool']['must'][]['exists'] = ['field' => $field];
        }

        $filter = $this->makeFilterCondition($conditions);

        $searchParams['body']['query']['bool']['must'][] = $filter;

        return $this->client->search($searchParams)['hits']['total'];
    }

    public function indexWithId($id, $content)
    {
        $params['index']    = $this->index;
        $params['type']     = $this->type;
        $params['id']       = $id;
        $params['body']     = $content;

        $response = ($this->client->index($params));

        if($response ['result'] == 'created'){
            return $this->find($response['_id']);
        }
        else {
            throw new EntityNotCreatedException();
        }

    }

    public function index($content)
    {
        $params['index'] = $this->index;
        $params['type'] = $this->type;
        $params['body'] = $content;

        $response = ($this->client->index($params));
        if($response ['result'] == 'created'){
            return $this->find($response['_id']);
        }
        else {
            throw new EntityNotCreatedException();
        }

    }


    public function update($id, $content)
    {

        if(isset($content['id']))unset($content['id']);
        if(isset($content[$this->uniqueKey])){
            if($this->verifyUniqueKey($content[$this->uniqueKey], $id) ){
                throw new MoreEntityWithSameAttributeException();
            }
        }

        $content['updated_at'] = time();
        unset($content['created_at']);



        $params['index'] = $this->index;
        $params['type'] = $this->type;
        $params['id'] = $id;
        $params['body'] = ['doc'=>$content];

        $response = ($this->client->update($params));

        if($response ['result'] == 'updated'){
            return $this->find($response['_id']);
        }
        elseif ($response ['result'] == 'noop'){
            throw new NoOperationNeededException();
        }
        else {
            throw new EntityNotUpdatedException();
        }
    }

    public function save()
    {

        if(!$this->verifyUniqueKey($this->model->{$this->uniqueKey})) {

            $attributes = $this->model->getAttributes();
            $attributes['updated_at'] = $attributes['created_at'] = time();
            return $this->index($attributes);
        }

        throw new MoreEntityWithSameAttributeException();
    }

    public function delete($id)
    {
        //PERFORM CONTROL ON ROLE, PERMISSION OR ASSOCIATED ENTITY (VENDOR/BUYER)
        //PERFORM SOFT-DELETE (set active = 0)
        dd('Sorry... TODO');
        return 'ko';
    }


    public function forceDestroy($id)
    {
        $params['index'] = $this->index;
        $params['type'] = $this->type;
        $params['id'] = $id;


        return $this->client->delete($params); //risponde true o false
    }

    public function indexExist()
    {
        return $this->client->indices()->exists(['index'=>$this->index]);
    }

    protected function getClassInstance($source)
    {
        $params = $source['_source'];
        $params['id'] =$source['_id'];
        return $this->model->forceFill($params);
    }

    public function verifyUniqueKey($value, $id = '')
    {
        try{
            $found = $this->getId([$this->uniqueKey => $value]);
        }catch (EntityNotFoundException $e){
            return false;
        }

        if($id != ''){
            if($id != $found )return true;
            else return false;
        }
        return true;
    }

}