<?php

namespace App\Repositories;


trait ELSQueryBuilderTrait
{

    /**
     * SYSTEM RESERVED KEY that will be searched by exact match
     * by the ELASTICSEARCH QUERY DSL
     *
     * @var array
     */
    public $reservedKey = [
        'source_type',
        'email',
        'permission',
        'role',
        'password',
        'code'
    ];


    /**
     * SYSTEM RESERVED KEY PORTION that will be searched by exact match
     * by the ELASTIC SEARCH QUERY DSL
     *
     * @var array
     */
    public $reservedPortionKey = [
        'id',
        'url',
        'application',
    ];


    /**
     * Generate the "QUERY" part of the array to send for ELASTIC SEARCH query DSL
     *
     * @param array $conditions
     * @return array
     */
    public function makeQueryCondition($conditions = ['query' => '*'])
    {
        return [
            'query_string' => [
                'query' => $conditions['query']
            ]
        ];
    }

    /**
     * Check if given value is a System reserved key
     *
     * @param $key
     * @return bool
     */
    public function checkReservedKey($key)
    {
        //se nella chiave non è contenuta una parola risarvata
        if ($this->strpos_array($key, $this->reservedPortionKey) === false) {
            foreach ($this->reservedKey as $item) {
                //se la chiave è esattamente una parola riservata ritona true (trovata)
                if ($item == $key) return true;
            }
            // se ha superato tutti i controlli è possible cercare questa chiave liberamente
            return false;
        } else {
            //se nella chiave è contenuta una parola risarvata ritorna ture (trovata)
            return true;
        }
    }

    /**
     * Funzione che genera la query in base al QUERY DSL di Elastic Search a partire da un array di input
     * applicando le logiche di sistema di EDM
     *
     * l'array conditions deve essere composto nel seguente modo:
     *
     * [
     *  'metadato riservato' => 'valore' oppure ['valore1','valore2'...] (produrrà una AND con la ricerca esatta del termine
     *                          in caso di array i valori sono in OR con le ricerche esatte dei termini)
     *  'metadato'=>'valore', (produrrà una AND con ricerca del termine che inizia con il valore fornito,
     *                         come le LIKE 'termine%' dei database SQL o se il flag "inside" è true all'interno
     *                         del metadato, LIKE '%termine%')
     *  'metadato che finisce per _DATE' => 'data in formato dd/mm/YYYY' per ottenere la ricerca del timestamp
     *  'DATE_RANGE'=>[
     *                  [
     *                    'FIELD'=>'metadato',
     *                    'FROM'=>'data in formato dd/mm/YYYY',
     *                    'TO'=>'data in formato dd/mm/YYYY'
     *                  ],
     *                  [
     *                    'FIELD'=>'metadato',
     *                    'FROM'=>'data in formato dd/mm/YYYY',
     *                    'TO'=>'data in formato dd/mm/YYYY'
     *                  ]
     *                  ...
     *                ]
     *  ]
     *
     *  Se il valore non è riservato e contiene uno spazio, viene automaticamente
     *      creato un array di elementi (uno per parola). La ricerca su questi elementi è una regexp
     *      che li cerca all'interno del metadato (non esatta)
     *
     *
     * @param $conditions array di condizioni in AND
     * @param string $searchType specifica il tipo di ricerca se deve essere differente da LIKE '%valore%'
     *                           accetta: start e ottiene LIKE 'valore%',
     *                                    end e ottiene LIKE '%valore'
     * @return array
     */
    public function makeFilterCondition($conditions, $searchType = '')
    {
        $filter = [];

        //Se c'è la chiave DATE_RANGE, il sistema presume che si sta cercando un "range" di date
        if(isset($conditions['DATE_RANGE'])){
            foreach ($conditions['DATE_RANGE'] as $item){
                //cerco i valori di inizio e fine del range
                if(isset($item['FROM'])) $element['range'][($item['FIELD'])]['gte'] = strtotime( str_replace('/', '-', $item['FROM']). ' Europe/Rome');
                if(isset($item['TO'])) $element['range'][($item['FIELD'])]['lte'] = strtotime( str_replace('/', '-', $item['TO']). ' Europe/Rome');
                if (isset($element)) {
                    $filter[] =$element;
                    unset($element);
                }
            }
            unset($conditions['DATE_RANGE']);
        }

        //Per ogni condizione, estrai chiave valore
        foreach ($conditions as $key => $value) {
            //Se ci sono più elementi nel valore, le condizioni saranno messe in OR
            if (is_array($value)) {
                foreach ($value as $data) {
                    $element['terms'][$key][] = $data;
                }

            }
            //controlla che la chiave non sia una parola riservata (true = riservata) oppure il valore contenuto è booleano
            elseif ($this->checkReservedKey($key) or ($value == 'true') or ($value == 'false')) {
                $element['term'][$key .'.keyword'] = $value;
                unset($conditions[$key]);
            }
            //Se finisce per "_DATE" è una data e va convertita in timestamp aggiungendo la regione
            elseif ($this->endWith($key,'_DATE')) {
                $value = str_replace('/', '-', $value);
                $element[]['term'][$key] = strtotime($value . ' Europe/Rome');
                unset($conditions[$key]);
            }
            else {
                //sostituisci i caratteri diversi da numeri e lettere con spazio e poi effettua il TRIM (lascia un solo spazio)
                $value = trim(preg_replace('/[^0-9a-zA-Z]+/', " ", $value));
                //se nel valore c'è almeno uno spazio
                if (preg_match('/\s+/', $value) == 1) {
                    //creo un array di elementi dalla stringa con spazi
                    $values = explode(" ", strtolower($value));
                    //aggiungo una regexp per ogni valore esploso
                    foreach ($values as $val) {
                        $element[]['regexp'] = [$key => '.*' . strtolower($val) . '.*'];
                    }
                } else {
                    $finalValue = strtolower($value);
                    if ($searchType == 'end') {
                        $finalValue = '.*' . $finalValue ;
                    }
                    elseif ($searchType == 'start') {
                        $finalValue = $finalValue . '.*' ;
                    }
                    else{
                        $finalValue = '.*' . $finalValue . '.*';
                    }
                    $element['regexp'] = [$key => $finalValue];
                }
            }


            if (isset($element)) {
                array_push($filter, $element);
                unset($element);
            }

        }
        return ($filter);
    }



    private function endWith($haystack, $needles)
    {
        return strlen($needles) - strlen($haystack) == strrpos($needles, $haystack);
    }

    /**
     * Strpos (Find the numeric position of the first occurrence of needle in the haystack string)
     * but in this implementation the needles as an array.
     * Also allows for a string, or an array inside an array.
     *
     * @param $haystack
     * @param $needles
     * @return bool|int
     */
    private function strpos_array($haystack, $needles)
    {
        if (is_array($needles)) {
            foreach ($needles as $str) {
                if (is_array($str)) {
                    return $this->strpos_array($haystack, $str);
                } else {
                    return strpos($haystack, $str);
                }
            }
        } else {
            return strpos($haystack, $needles);
        }
    }
}