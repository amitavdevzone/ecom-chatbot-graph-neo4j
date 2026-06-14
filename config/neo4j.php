<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Neo4j Bolt Connection
    |--------------------------------------------------------------------------
    |
    | Used by Neo4jService to build the Laudis PHP client. In Docker, the host
    | is the compose service name (neo4j), not localhost.
    |
    */

    'url' => env('NEO4J_URL', 'bolt://neo4j:7687'),

    'username' => env('NEO4J_USERNAME', 'neo4j'),

    'password' => env('NEO4J_PASSWORD', 'password'),

];
