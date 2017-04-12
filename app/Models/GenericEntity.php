<?php

namespace App\Models;

use App\Repositories\ELSBaseRepository;
use Elasticquent\ElasticquentTrait;
use App\Repositories\RepositoryTrait;
use Illuminate\Database\Eloquent\Model;

class GenericEntity extends Model
{
    use ElasticquentTrait, RepositoryTrait;

    private $repo;
    private $index;
    private $type;

    function __construct($type, $index)
    {
        parent::__construct();
        $this->index = $index;
        $this->type = $type;
        $this->repo = new ELSBaseRepository($this, $type, $index, 'code', false);
    }

    function getIndexName()
    {
        return $this->index;
    }

    function getTypeName()
    {
        return $this->type;
    }

    protected $fillable = [];


    public function deleteEntity($id)
    {
        return $this->repo->forceDestroy($id);
    }
}