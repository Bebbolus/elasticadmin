# ElasticAdmin
A web front end for browsing and interacting with Elastic Search for Laravel PHP Framework

## ElasticAdmin Requirements
ElasticAdmin is born on top of Elastiquent, you must be running at least Elasticsearch 1.0. Elasticsearch 0.9 and below will not work and are not supported.


run commands:

    > composer install


The .env of DEFAULT DEVELOPMENT ENVIRONMENT:

       APP_ENV=local
       APP_KEY=base64:eYkj/0FsuEPVGpn6gadTuegrzje33kGhEwarfrdN4/4=
       APP_DEBUG=true
       APP_LOG_LEVEL=debug
       APP_URL=http://localhost
       
       APP_NAME=ELSADMIN
      
      
       BROADCAST_DRIVER=log
       CACHE_DRIVER=file
       SESSION_DRIVER=file
       QUEUE_DRIVER=sync
       
       ELS_MAX_RESULT=20
    
       ELS_SERVER=localhost
    
    
**_NB_**

> edit the .env file with the right configuration parameters
> for the application you will develp 
> i.e. all the ELS_* parameters, etc...

_**You need to set-up your own Index, your own Server and configure it in .env (ELS_SERVER, ELS_INDEX_USER)**_

run key re-generation command:

    > php artisan key:generate