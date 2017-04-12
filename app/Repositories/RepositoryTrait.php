<?php

namespace App\Repositories;


trait RepositoryTrait
{
    private $repo;

    function __construct()
    {
            if (!isset($this->key)) $this->key  = 'code';
            parent::__construct();
            $this->repo = new ELSBaseRepository($this, $this->getTypeName(), $this->getIndexName(), $this->key);
    }

    /*
     * PROPERTY RETRIEVE FUNCTION
     */

    public function getUniqueKey()
    {
        return $this->key;
    }

    public function getUniqueKeyValue()
    {
        return $this->{$this->key};
    }

    //MOdificato
    public function getCode()
    {
        return $this->code;
    }

    public function getElsId()
    {
        return $this->getAttributes()['id'];
    }

    /*
     * SEARCH FUNCTION
     */

    public function find($id)
    {
        return $this->repo->find($id);
    }

    public function quest($term, $page = 0)
    {
        return $this->repo->search($term, $page);
    }

    public function findByKey($keyValue)
    {
        return $this->repo->findByKey($keyValue);
    }

    public function get($conditions = [], $requiredField = [], $page = 0)
    {
        return $this->repo->get($conditions, $requiredField, $page);
    }

    public function getId($conditions = [], $requiredField = [])
    {
        return $this->repo->getId($conditions, $requiredField);
    }

    public function count($conditions = [], $requiredField = [])
    {
        return $this->repo->count($conditions, $requiredField);
    }

    /*
     * CRUD FUNCTION
     */

    public function indexWithId($content)
    {
        return $this->repo->indexWithId($this->getElsId(), $content);
    }

    public function index($content)
    {
        return $this->repo->index($content);
    }

    public function update(array $attributes = [], array $options = [])
    {
        return $this->repo->update($this->getElsId(), $attributes);
    }

    public function save(array $options = [])
    {
        return $this->repo->save();
    }

    public function delete()
    {
        return $this->repo->delete($this->getElsId());
    }

    public function forceDestroy()
    {
        return $this->repo->forceDestroy($this->getElsId());
    }

    public function indexExist()
    {
        return $this->repo->indexExist();
    }

    public function verifyUniqueKey($value)
    {
        return $this->repo->verifyUniqueKey($value);
    }

}