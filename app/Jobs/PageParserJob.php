<?php

namespace App\Jobs;

use Illuminate\Support\Facades\DB;

class PageParserJob extends Job
{
    private $url;
    
    /**
     * Create a new job instance.
     *
     * @return void
     */

    public function __construct($url)
    {
        $this->url = $url;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $client = app('client');
        $promise = $client->getAsync($this->url);
        $promise->then(
            function($response) {  
                $body = $response->getBody();
                $contentLength = $response->getHeader('Content-Length')[0] ?? strlen($body);
                DB::table('domains')->insert(
                    [
                        'name' => $this->url,
                        'content_length' => $contentLength,
                        'status_code' => $response->getStatusCode(),
                        'body' => $body
                    ]
                );
            }
        );
        $promise->wait();
        return;
    }
}
