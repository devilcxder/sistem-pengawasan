<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\TweetRequest;
use App\Models\Tweet;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\CRUD\app\Library\Widget;
use Illuminate\Support\Facades\Auth;

/**
 * Class TweetCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class TweetCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;    

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     * 
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\Tweet::class);
        CRUD::setRoute('/tweet');
        CRUD::setEntityNameStrings('tweet', 'tweets');
    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {        

        /**
         * Columns can be defined using the fluent syntax or array syntax:
         * - CRUD::column('price')->type('number');
         * - CRUD::addColumn(['name' => 'price', 'type' => 'number']); 
         */
        CRUD::column('id');
        CRUD::column('tweet');
        CRUD::addColumn([
            // any type of relationship
            'name'         => 'detail_tweet', // name of relationship method in the model
            'type'         => 'relationship',
            'label'        => 'Label', // Table column heading
            // OPTIONAL
            // 'entity'    => 'detail_tweet', // the method that defines the relationship in your Model
            'attribute' => 'label', // foreign key attribute that is shown to user
            // 'model'     => App\Models\DetailTweet::class, // foreign key model
        ]);
        $this->crud->denyAccess('update');
        $this->crud->denyAccess('delete');        
        $this->crud->denyAccess('create');        

        // Filter Label
        $this->crud->addFilter([
            'type' => 'dropdown',
            'name' => 'label',
            'label' => 'Emosi',
        ],
        function() {
            return Tweet::select('label')->join('detail_tweets','tweets.id','=','tweet_id')->distinct()->get()->pluck('label', 'label')->toArray();
        },
        function($value) {            
            $this->value = $value;
            $this->crud->addClause('join','detail_tweets', function($query){
                $query->on('tweets.id', '=', 'tweet_id')
                       ->where('label', '=', $this->value);
            });
        });
    }

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(TweetRequest::class);



        /**
         * Fields can be defined using the fluent syntax or array syntax:
         * - CRUD::field('price')->type('number');
         * - CRUD::addField(['name' => 'price', 'type' => 'number'])); 
         */
    }

    /**
     * Define what happens when the Update operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     * @return void
     */
    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }

    protected function setupShowOperation()
    {
        $this->crud->set('show.setFromDb', false);
        $this->crud->addColumn([
            'name' => 'post_id',
            'label' => 'Tweet ID'
        ]);
        $this->crud->addColumn([
            'name' => 'tweet',
            'label' => 'Tweet'
        ]);                        
        CRUD::addColumn([
            // any type of relationship
            'name'         => 'detail_tweet', // name of relationship method in the model
            'type'         => 'relationship',
            'label'        => 'Label', // Table column heading
            // OPTIONAL
            // 'entity'    => 'detail_tweet', // the method that defines the relationship in your Model
            'attribute' => 'label', // foreign key attribute that is shown to user
            // 'model'     => App\Models\DetailTweet::class, // foreign key model
        ]);        
        CRUD::addColumn([
            // any type of relationship
            'name'         => 'tweet_created', // name of relationship method in the model
            'type'         => 'relationship'    ,
            'label'        => 'Tanggal', // Table column heading
            // OPTIONAL
            // 'entity'    => 'detail_tweet', // the method that defines the relationship in your Model
            'attribute' => 'created_at', // foreign key attribute that is shown to user
            // 'model'     => App\Models\DetailTweet::class, // foreign key model
        ]);
        $this->crud->denyAccess('delete');
    }
}
