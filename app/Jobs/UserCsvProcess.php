<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Bus\Batchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class UserCsvProcess implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $header;
    public $data;

    public function __construct($data, $header)
    {
        $this->data = $data;
        $this->header = $header;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            if ($this->data){
                foreach ($this->data as $value) {
                    User::create($value);
                }
                //dd('success');
                // mail function here
                $name = "Md Azaharuddin";
                $name1 = "Sunny";
                $email = "sunnyazahar@gmail.com";
                $email1 = "azaharsunny@gmail.com";
                $message = "Your data has been successfully submitted.";
                $subject = "Laravel test";
                sendMail($name, $email,  rand(1111111111,9999999999), $message, $subject);
                sendMail($name1, $email1,rand(1111111111,9999999999), $message, $subject);
            }else{
                return "Data not found";
            }
        }catch (\Exception $e)
        {
            return $e->getMessage();
        }
    }
}
