<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\DatasetRequest;
use App\Imports\DatasetsImport;
use App\Models\Dataset;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Maatwebsite\Excel\Facades\Excel;

use function PHPSTORM_META\map;

/**
 * Class DatasetCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class DatasetCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation {
        store as traitStore;
    }
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\BulkDeleteOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     * 
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\Dataset::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/dataset');
        CRUD::setEntityNameStrings('dataset', 'dataset');
        CRUD::orderBy('id', 'ASC');
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
        CRUD::column('text');
        CRUD::column('label');        
        $this->crud->denyAccess('update');
        $this->crud->addButtonFromModelFunction('top', 'template_dataset', 'downloadTemplate', 'end');

        // select2 filter
        $this->crud->addFilter([
            'type' => 'dropdown',
            'name' => 'category_id',
            'label' => 'Kategori',
        ],
        function() {
            return Dataset::select('category_id','category')->join('categories','category_id','=','categories.id')->distinct()->get()->pluck('category', 'category_id')->toArray();
        },
        function($value) {
            $this->crud->addClause('where', 'category_id', $value);
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
        CRUD::setValidation(DatasetRequest::class);

        /**
         * Fields can be defined using the fluent syntax or array syntax:
         * - CRUD::field('price')->type('number');
         * - CRUD::addField(['name' => 'price', 'type' => 'number'])); 
         */
        CRUD::addfield([
            'name' => 'category',
            'label' => 'Kategori',
            'type' => 'text',
            'attributes' => [
                'placeholder' => 'Masukkan kategori dataset'
            ]
        ]);

        CRUD::addField([   // Browse
            'name'      => 'dataset',
            'label'     => 'File Excel',
            'type'      => 'upload',
            'upload'    => true,
            'disk'      => 'uploads'
        ]);        

        CRUD::replaceSaveActions(
            [
                'name' => 'Simpan',
                'visible' => function ($crud) {
                    return true;
                },
                'redirect' => function ($crud, $request, $itemId) {
                    return $crud->route;
                },
            ],
        );
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
        $this->crud->denyAccess('delete');
    }

    public function store(DatasetRequest $request)
    {
        
        Excel::import(new DatasetsImport(request()->category), request()->file('dataset'));
        \Alert::add('success', 'Dataset sedang diproses')->flash();
        return \Redirect::to($this->crud->route);
    }
}
