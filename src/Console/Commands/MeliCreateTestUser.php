<?php

namespace WebDEV\Meli\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use PhpParser\Node\Stmt\TryCatch;
use WebDEV\Meli\Services\MeliApiService;

class MeliCreateTestUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'meli:create-test-user {state} {siteId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new test user at MercadoLibre';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @throws Exception
     */
    public function handle()
    {
        try {
            $this->line('Creating new test user...');
            $state = $this->argument('state');
            $siteId = $this->argument('siteId');
            $service = new MeliApiService($state);
            $query = $service->createTestUser($siteId);
            if(!$query->response->id) {
                throw new Exception($query->response->message ?? 'UNDEFINED ERROR!');
            }
            $this->info("DONE! New user created successfully:\n");
            $this->line('ID: ' . $query->response->id);
            $this->line('Nickname: ' . $query->response->nickname);
            $this->line('Password: ' . $query->response->password);
            $this->line('Email: ' . $query->response->email . "\n");
            exit(0);
        } catch(Exception $ex) {
            $this->error('Unable to create new test user:');
            $this->error('--> ' . $ex->getMessage());
            exit(1);
        }
    }
}
