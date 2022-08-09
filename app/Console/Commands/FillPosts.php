<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Post;
use App\Models\User;
class FillPosts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:posts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get post from api to fill the post table';

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
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $postsApi = collect(Http::get('https://jsonplaceholder.typicode.com/posts')->json())->take(50);
        $usersApi = collect(Http::get('https://jsonplaceholder.typicode.com/users/')->json());

        try{            
            foreach($postsApi as $postApi):
                $post = Post::updateOrCreate(
                    [
                        "body" => $postApi['body']
                    ],
                    [
                        "id" => $postApi['id'],
                        "user_id" => $postApi['userId'],
                        "title" => $postApi['title'],
                        "rating" => $this->calculateRating($postApi)
                    ]
                );
                
                $userApi = $usersApi->whereIn('id', $post->user_id)->first();
                $user = User::firstOrCreate(
                    [
                        "email" => $userApi['email']
                    ],
                    [
                        "id" => $userApi['id'],
                        "name" => $userApi['name'],
                        "city" => $userApi['address']['city']
                    ]
                );
            endforeach;
        }catch(\Exception $e){
            echo $e->getMessage();
        }
    }
    public function calculateRating($post){
        $titleWords = explode(" ", $post['title']);
        $bodyWords = explode(" ", $post['body']);
        return sizeof($titleWords) * 2 + sizeof($bodyWords);
    }
}
