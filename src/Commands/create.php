<?php

if ($argc == 1) {
    die('Error: This process needs a name of model, for instance: CompanyFile');
}
// Model Name
$modelName = $argv[1];

// Resolve file types to create
$aliasToCreate = isset($argv[2]) ? $argv[2] : null;
if ($aliasToCreate == '_') {
    $aliasToCreate = 'IEGMR';
} elseif ($aliasToCreate == '*') {
    $aliasToCreate = 'IEGMRDQCicefxwal';
}

$nameProperties = isset($argv[3]) ? $argv[3] : null;

// Resolve possible names models, in all cases that it need
$entityName = lcfirst($modelName);
$initLetter = substr($entityName, 0, strlen($entityName) - 1);
$lastLetter = substr($entityName, -1);
switch ($lastLetter) {
    case 'y';
        $entityNamePlural = $initLetter . 'ies';
        $entitySlugName = snakeCase($initLetter . 'ies');
        $entitySnakeName = snakeCase($initLetter . 'ies', '_');
        break;
    case 's';
        $entityNamePlural = $initLetter . 's';
        $entitySlugName = snakeCase($initLetter . 's');
        $entitySnakeName = snakeCase($initLetter . 's', '_');
        break;
    default:
        $entityNamePlural = $entityName . 's';
        $entitySlugName = snakeCase($modelName . 's');
        $entitySnakeName = snakeCase($modelName . 's', '_');
        break;
}

$modelNamePlural = pascalCase(snakeCase($entityNamePlural, '_'), false, '_', ' ');
$entityPermissionName = strtoupper($entitySnakeName);
// Default values when not exist properties
$entityFields = '';
$entityRelationMethods = '';
$entityMethods = ' * @method $this|string ($ = null)';
$entityEntityUse = '';
$entityModelUseDeclaration = '';
$entityModelUse = '';
$entityFiltersDataTable = '            ->setColumn(\'id\');';
$entityInfo = <<<EOD
    <div class="form-group">
        <label class="col-md-2 control-label" for="id">
            @lang( '{$entitySnakeName}.columns.id' )
        </label>

        <div class="col-md-10">
            <strong>{{ \${$entityName}->id() }}</strong>
        </div>
    </div>

EOD;
$entityInputs = <<<EOD
<div class="form-group @if( \$errors->has( 'id' ) ) has-error @endif">
    <label class="col-md-2 control-label" for="id">
        @lang( '{$entitySnakeName}.columns.id' )
    </label>

    <div class="col-md-10">
        {!! Form::text( 'id', null, [
            'id' => 'id',
            'class' => 'form-control',
            'required',
        ]) !!}

        @if( \$errors->has( 'id' ) )
            <p class="help-block">{{ \$errors->first('id') }}</p>
        @endif
    </div>
</div>

EOD;

$entityColumns = '';

if ($nameProperties) {
    // Resolve method entity setter|getter properties
    $properties = explode(',', $nameProperties);
    // Clean duplicate
    $properties = array_unique($properties);
    $skipProperties = ['id', 'created_by', 'updated_by', 'deleted_by', 'created_at', 'updated_at', 'deleted_at'];
    $ignoredProperties = ['have_information_related', 'dictionary_id'];
    $fields = [];
    $fields[] = sprintf("            '%s'", 'id');
    $methods = [];
    $entityUse = [];
    $relationMethods = [];
    $filtersDataTable = [];
    $info = [];
    $inputs = [];
    $columns = [];
    foreach ($properties as $property) {
        $property = strtolower($property);
        $setterProperty = pascalCase($property);
        $typeProperty = propertyIsNumeric($property) ? 'int' : 'string';
        $methods[] = sprintf(' * @method $this|%s %s($%s = null)', $typeProperty, $setterProperty, $setterProperty);
        $translation = pascalCase($property, false, '_', ' ');
        $textInfo = "\${$entityName}->{$setterProperty}()";

        if (in_array($property, $skipProperties)) {
            continue;
        }

        $fields[] = sprintf("            '%s'", $property);

        if ($property === 'name' and in_array('dictionary_id', $properties)) {
            // Field Translation
            $fields[] = sprintf("            '%s_translated'", $property);
            $methods[] = sprintf(' * @method %s %sTranslated()', $typeProperty, $setterProperty);
            $textInfo = "\${$entityName}->{$setterProperty}Translated()";
            $entityModelUseDeclaration = "\nuse App\Traits\NameTranslatedAttributeTrait;";
            $entityModelUse = "\n    use NameTranslatedAttributeTrait;";
        }

        if (in_array($property, $ignoredProperties)) {
            continue;
        }

        $isRelation = substr($property, -3) === '_id';
        $isDate = substr($property, -3) === '_at';
        $isStatus = $property === 'status';

        if ($isRelation) {
            $temp = substr($property, 0, -3);
            $entityRelationName = pascalCase($temp, false);
            $translation = pascalCase($temp, false, '_', ' ');
            $entityManagerName = lcfirst($entityRelationName);
            $textInfo = "\${$entityName}->{$entityManagerName}()->nameTranslated()";
            $entityUse['use App\Managers\Manager;'] = 'use App\Managers\Manager;';
            $relationMethods[] = <<<EOD
    /**
     * @return {$entityRelationName}Entity
     */
    public function {$entityManagerName}()
    {
        return Manager::{$entityManagerName}()->getById(\$this->{$setterProperty}());
    }

EOD;
        } else if ($isDate) {
            $relationMethods[] = <<<EOD
    /**
     * @return null|string
     */
    public function {$setterProperty}Diff()
    {
        return diffForHumansOf(\$this->{$setterProperty}());
    }

EOD;
        } else if ($isStatus) {
            $statusInterfaceName = pascalCase($entityName, false);
            $entityUse[] = "use App\Constants\\{$statusInterfaceName}Status;";
            $textInfo = "\${$entityName}->{$setterProperty}()";
            $textInfo = "trans('{$entitySnakeName}.status.' . \${$entityName}->{$setterProperty}())";
            $relationMethods[] = <<<EOD
    /**
     * @return bool
     */
    public function is()
    {
        return \$this->{$setterProperty}() === {$statusInterfaceName}Status::;
    }

EOD;
        } else {
            $filtersDataTable[] = "->setColumn('{$property}')";
        }

        $propertyLabel = (!$isRelation) ? $property : str_replace('_id', '', $property);

        $info[] = <<<EDO
    <div class="form-group">
        <label class="col-md-2 control-label" for="{$property}">
            @lang( '{$entitySnakeName}.columns.{$propertyLabel}' )
        </label>

        <div class="col-md-10">
            <strong>{{ {$textInfo} }}</strong>
        </div>
    </div>
EDO;

        $inputs[] = <<<EOD
<div class="form-group @if( \$errors->has( '{$property}' ) ) has-error @endif">
    <label class="col-md-2 control-label" for="{$property}">
        @lang( '{$entitySnakeName}.columns.{$propertyLabel}' )
    </label>

    <div class="col-md-10">
        {!! Form::text( '{$property}', null, [
            'id' => '{$property}',
            'class' => 'form-control',
            'required',
        ]) !!}

        @if( \$errors->has( '{$property}' ) )
            <p class="help-block">{{ \$errors->first('{$property}') }}</p>
        @endif
    </div>
</div>
EOD;

        $columns[] = sprintf("'%s' => '%s',", $propertyLabel, $translation);
    }

    if (count($fields) > 0) {
        $entityFields = implode(",\n", $fields);
    }

    if (count($methods) > 0) {
        $entityMethods = implode("\n", $methods);
    }

    if (count($entityUse) > 0) {
        $entityEntityUse = "\n" . implode("\n", $entityUse);
    }

    if (count($relationMethods) > 0) {
        $entityRelationMethods = "\n" . implode("\n", $relationMethods);
    }

    if (count($filtersDataTable) > 0) {
        $entityFiltersDataTable = sprintf('%s;', implode("\n            ", $filtersDataTable));
    }

    if (count($info) > 0) {
        $entityInfo = implode("\n\n", $info);
    }

    if (count($inputs) > 0) {
        $entityInputs = implode("\n\n", $inputs);
    }

    if (count($columns) > 0) {
        $entityColumns = implode("\n        ", $columns);
    }
}

$pathApp = getcwd();
$user = 'freddie';
$group = 'www-data';
$setup = [];

$setup['interface']['alias'] = 'I';
$setup['interface']['name'] = $modelName . 'Repository';
$setup['interface']['namespace'] = 'App\Contracts\Repositories';
$setup['interface']['tpl'] = <<<EOD
<?php

namespace {$setup['interface']['namespace']};

use FreddieGar\Base\Contracts\Interfaces\RepositoryInterface;

interface {$setup['interface']['name']} extends RepositoryInterface
{
}

EOD;

$setup['entity']['alias'] = 'E';
$setup['entity']['name'] = $modelName . 'Entity';
$setup['entity']['namespace'] = 'App\Entities';
$setup['entity']['tpl'] = <<<EOD
<?php

namespace {$setup['entity']['namespace']};

use FreddieGar\Base\Contracts\Commons\EntityLaravel;{$entityEntityUse}

/**
 * Class {$setup['entity']['name']}
 *
{$entityMethods}
 *
 * @package App\Entities
 */
class {$setup['entity']['name']} extends EntityLaravel
{
    /**
     * @inheritdoc
     */
    protected function fields()
    {
        return [
{$entityFields}
        ];
    }{$entityRelationMethods}
}

EOD;

$setup['manager']['alias'] = 'G';
$setup['manager']['name'] = $modelName . 'Manager';
$setup['manager']['namespace'] = 'App\Managers';
$setup['manager']['tpl'] = <<<EOD
<?php

namespace {$setup['manager']['namespace']};

use {$setup['interface']['namespace']}\\{$setup['interface']['name']};
use {$setup['entity']['namespace']}\\{$setup['entity']['name']};
use FreddieGar\Base\Contracts\Commons\ManagerLaravel;
use Illuminate\Http\Request;

/**
 * Class {$setup['manager']['name']}
 *
 * @method \$this|{$setup['entity']['name']} entity(\$entity = null)
 * @method \$this|{$setup['interface']['name']} repository(\$repository = null)
 * @method {$setup['entity']['name']} create(array \$attributes = [])
 * @method {$setup['entity']['name']} update(\$id, array \$attributes = [])
 * @method {$setup['entity']['name']} getById(\$id)
 *
 * @package {$setup['manager']['namespace']}
 */
class {$setup['manager']['name']} extends ManagerLaravel implements {$setup['interface']['name']}
{
    /**
     * ModuleManager constructor.
     * @param Request \$request
     * @param {$setup['interface']['name']} \$repository
     * @param {$setup['entity']['name']} \$entity
     */
    public function __construct(Request \$request, {$setup['interface']['name']} \$repository, {$setup['entity']['name']} \$entity)
    {
        \$this->request(\$request);
        \$this->repository(\$repository);
        \$this->entity(\$entity);
    }
}

EOD;

$setup['model']['alias'] = 'M';
$setup['model']['name'] = $modelName;
$setup['model']['namespace'] = 'App\Models';
$setup['model']['tpl'] = <<<EOD
<?php

namespace {$setup['model']['namespace']};

use FreddieGar\Base\Contracts\Interfaces\BlameControlInterface;
use FreddieGar\Base\Traits\BlameControlTrait;
use Illuminate\Database\Eloquent\SoftDeletes;{$entityModelUseDeclaration}

class {$setup['model']['name']} extends ModelBase implements BlameControlInterface
{
    use BlameControlTrait;{$entityModelUse}
    use SoftDeletes;
}

EOD;

$setup['repository']['alias'] = 'R';
$setup['repository']['name'] = 'Eloquent' . $modelName . 'Repository';
$setup['repository']['namespace'] = 'App\Repositories\Eloquent';
$setup['repository']['tpl'] = <<<EOD
<?php

namespace {$setup['repository']['namespace']};

use {$setup['interface']['namespace']}\\{$setup['interface']['name']};
use {$setup['model']['namespace']}\\{$setup['model']['name']};
use FreddieGar\Base\Repositories\Eloquent\EloquentRepository;

class {$setup['repository']['name']} extends EloquentRepository implements {$setup['interface']['name']}
{
    protected \$model;

    public function __construct({$setup['model']['name']} \$model)
    {
        \$this->model = \$model;
    }
}

EOD;

$setup['datatable']['alias'] = 'D';
$setup['datatable']['name'] = $modelName . 'DataTable';
$setup['datatable']['namespace'] = 'App\DataTables';
$setup['datatable']['tpl'] = <<<EOD
<?php

namespace {$setup['datatable']['namespace']};

use App\Constants\PermissionSlug;
use {$setup['manager']['namespace']}\\{$setup['manager']['name']};
use Illuminate\Support\Facades\Auth;

class {$setup['datatable']['name']} extends DataTableBase
{
    /**
     * Display ajax response.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajax()
    {
        return \$this->getEloquentEngine()
            ->addColumn('actions', \$this->getColumnAction(\$this->view() . '.actions'))
            ->make(true);
    }

    /**
     * Setup
     */
    protected function config()
    {
        \$this->view('{$entitySlugName}')
            ->route(route(\$this->view() . '.index'))
            ->model({$setup['manager']['name']}::class)
            {$entityFiltersDataTable}
        
        if (Auth::user()->may(PermissionSlug::read(\$this->view())) ||
            Auth::user()->may(PermissionSlug::edit(\$this->view())) ||
            Auth::user()->may(PermissionSlug::delete(\$this->view()))
        ) {
            \$this->setAction();
        }
    }
}

EOD;

$setup['request']['alias'] = 'Q';
$setup['request']['name'] = $modelName . 'Request';
$setup['request']['namespace'] = 'App\Http\Requests';
$setup['request']['tpl'] = <<<EOD
<?php

namespace {$setup['request']['namespace']};

use Illuminate\Foundation\Http\FormRequest;

class {$setup['request']['name']} extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        \$id = \$this->route('{$entitySnakeName}') ? \$this->route('{$entitySnakeName}') : 0;

        return [
        ];
    }

    public function attributes()
    {
        return trans('{$entitySnakeName}.columns');
    }
}

EOD;

$setup['controller']['alias'] = 'C';
$setup['controller']['name'] = $modelName . 'Controller';
$setup['controller']['namespace'] = 'App\Http\Controllers';
$setup['controller']['tpl'] = <<<EOD
<?php

namespace {$setup['controller']['namespace']};

use App\Constants\PermissionSlug;
use {$setup['datatable']['namespace']}\\{$setup['datatable']['name']};
use {$setup['request']['namespace']}\\{$setup['request']['name']};
use {$setup['manager']['namespace']}\\{$setup['manager']['name']};
use App\Managers\Manager;
use Illuminate\Http\Request;

class {$setup['controller']['name']} extends Controller
{
    const ACTION_BASE = PermissionSlug::{$entityPermissionName};

    /**
     * @return {$setup['manager']['name']}
     */
    protected function manager()
    {
        return Manager::{$entityName}();
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request \$request
     * @param {$setup['datatable']['name']} \$dataTable
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function index(Request \$request, {$setup['datatable']['name']} \$dataTable)
    {
        if (\$request->ajax()) {
            return \$dataTable->ajax();
        }

        parent::logActionIndex();

        return view(\$this->base('index'), [
            'html' => \$dataTable->html(),
            'formFilters' => \$dataTable->formFilters(),
            'entityName' => \$this->entityName(true),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view(\$this->base('create'), [
            'entityName' => \$this->entityName(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param {$setup['request']['name']} \$request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store({$setup['request']['name']} \$request)
    {
        \$entity = \$this->setActionCreating()->save(\$request->all());

        return \$this->redirectToShow(\$entity->id());
    }

    /**
     * Display the specified resource.
     *
     * @param  int \$id
     * @return \Illuminate\Http\Response
     */
    public function show(\$id)
    {
        \${$entityName} = \$this->manager()->getById(\$id);
        \$entityName = \$this->entityName();

        parent::logActionRead();

        return view(\$this->base('show.info'), compact('{$entityName}', 'entityName'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int \$id
     * @return \Illuminate\Http\Response
     */
    public function edit(\$id)
    {
        \${$entityName} = \$this->manager()->getById(\$id);
        \$entityName = \$this->entityName();

        return view(\$this->base('edit'), compact('{$entityName}', 'entityName'));
    }

    /**
     * Update the specified resource in storage.
     * @param {$setup['request']['name']} \$request
     * @param \$id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update({$setup['request']['name']} \$request, \$id)
    {
        \$this->setActionEditing()->save(\$request->all(), \$id);

        return \$this->redirectToShow(\$id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int \$id
     * @return \Illuminate\Http\Response
     */
    public function destroy(\$id)
    {
        \$this->setActionDeleting();

        if (\$this->manager()->getById(\$id)->haveInformationRelated() || !\$this->manager()->delete(\$id)) {
            \$this->logActionFailed();

            return \$this->redirectToWarning();
        }

        \$this->logActionSuccess();

        return \$this->redirectToIndex();
    }
}

EOD;

$setup['_info']['alias'] = 'i';
$setup['_info']['name'] = 'info.blade';
$setup['_info']['namespace'] = "resources/views/{$entitySlugName}/show";
$setup['_info']['tpl'] = <<<EOD
@php use App\Constants\PermissionSlug;
/** @var {$setup['entity']['namespace']}\\{$setup['entity']['name']} \${$entityName} */
@endphp
@extends( '{$entitySlugName}.show' )

@section( 'tab-info' )
{$entityInfo}
    <div class="form-group">
        <div class="col-md-12">
            @permission(PermissionSlug::edit(PermissionSlug::{$entityPermissionName}))
            @include('partials.edit_button', ['route' => route( '{$entitySlugName}.edit', \${$entityName}->id())])
            @endpermission

            @permission(PermissionSlug::delete(PermissionSlug::{$entityPermissionName}))
            @if(!\${$entityName}->haveInformationRelated())
                @include('partials.delete_button', ['route' => route( '{$entitySlugName}.destroy', \${$entityName}->id()), compact('entityName')])
            @endif
            @endpermission
        </div>
</div>
@endsection
EOD;

$setup['_create']['alias'] = 'c';
$setup['_create']['name'] = 'create.blade';
$setup['_create']['namespace'] = "resources/views/{$entitySlugName}";
$setup['_create']['tpl'] = <<<EOD
@extends('layouts.app')

@section('content')
    <div class="panel panel-default">
        <header class="panel-heading">
            <h2 class="panel-title">
                <i class="fa fa-"></i>
                @lang( 'generals.title.create', compact('entityName'))
            </h2>
        </header>

        {!! Form::model( null, [
            'route' => ['{$entitySlugName}.store'],
            'method' => 'post',
        ]) !!}

        <div class="panel-body">
            @include('{$entitySlugName}.create_or_edit_form')
        </div>

        <footer class="panel-footer">
            @include( 'partials.save_or_cancel_button', ['back' => route('{$entitySlugName}.index')] )
        </footer>

        {!! Form::close() !!}
    </div>
@endsection
EOD;

$setup['_edit']['alias'] = 'e';
$setup['_edit']['name'] = 'edit.blade';
$setup['_edit']['namespace'] = "resources/views/{$entitySlugName}";
$setup['_edit']['tpl'] = <<<EOD
@php /** @var {$setup['entity']['namespace']}\\{$setup['entity']['name']} \${$entityName} */ @endphp
@extends('layouts.app')

@section('content')
    <div class="panel panel-default">
        <header class="panel-heading">
            <h2 class="panel-title">
                <i class="fa fa-"></i>
                @lang( 'generals.title.edit', compact('entityName'))
            </h2>
        </header>

        {!! Form::model( \${$entityName}, [
            'route' => ['{$entitySlugName}.update', \${$entityName}->id()],
            'method' => 'put',
        ]) !!}

        <div class="panel-body">
            @include('{$entitySlugName}.create_or_edit_form')
        </div>

        <footer class="panel-footer">
            @include( 'partials.save_or_cancel_button', ['back' => route('{$entitySlugName}.index')] )
        </footer>

        {!! Form::close() !!}
    </div>
@endsection
EOD;

$setup['_form']['alias'] = 'f';
$setup['_form']['name'] = 'create_or_edit_form.blade';
$setup['_form']['namespace'] = "resources/views/{$entitySlugName}";
$setup['_form']['tpl'] = <<<EOD
@php /** @var {$setup['entity']['namespace']}\\{$setup['entity']['name']} \${$entityName} */ @endphp
{$entityInputs}
EOD;

$setup['_index']['alias'] = 'x';
$setup['_index']['name'] = 'index.blade';
$setup['_index']['namespace'] = "resources/views/{$entitySlugName}";
$setup['_index']['tpl'] = <<<EOD
@php use App\Constants\PermissionSlug;
/** @var {$setup['datatable']['namespace']}\\{$setup['datatable']['name']} \$html */
@endphp
@extends('layouts.app')

@section('content')
    <div class="panel panel-default">
        <header class="panel-heading">
            <h2 class="panel-title">
                <i class="fa fa-"></i>
                @lang( 'generals.title.index', compact('entityName'))

                @permission(PermissionSlug::create(PermissionSlug::{$entityPermissionName}))
                @include('partials.create_button', ['route' => route('{$entitySlugName}.create'), compact('entityName')])
                @endpermission

                @include('filter-components.show_button')
            </h2>
        </header>

        <div class="panel-body">
            {!! \$html->table([ 'class' => 'datatable-list table table-hover table-bordered table-striped mb-none' ]) !!}
        </div>
    </div>
@endsection

@push('scripts')
{!! \$html->scripts() !!}
@endpush
EOD;

$setup['_show']['alias'] = 'w';
$setup['_show']['name'] = 'show.blade';
$setup['_show']['namespace'] = "resources/views/{$entitySlugName}";
$setup['_show']['tpl'] = <<<EOD
@php use App\Constants\PermissionSlug;
/** @var {$setup['entity']['namespace']}\\{$setup['entity']['name']} \${$entityName} */
\$tabs = [
    'INFO' => '{$entitySlugName}.show',
];

\$isTabInfo = Request::route()->getName() === \$tabs[ 'INFO' ] ? 'active' : '';
@endphp
@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="container-fluid">
            <ul class="nav nav-tabs">
                <li>
                    @include('partials.back_button', ['route' => route('{$entitySlugName}.index')])
                </li>

                <li class="{{ \$isTabInfo }}">
                    <a href="{{ !\$isTabInfo ? route( \$tabs[ 'INFO' ], \${$entityName}->id() ) : '#' }}">
                        @lang( 'generals.title.index', compact('entityName') )
                    </a>
                </li>
                
                @permission(PermissionSlug::create(PermissionSlug::{$entityPermissionName}))
                <li>
                    @include('partials.add_new_button', ['route' => route('{$entitySlugName}.create'), compact('entityName')])
                </li>
                @endpermission
            </ul>

            <div class="tab-content">
                @permission(PermissionSlug::index(PermissionSlug::{$entityPermissionName}))
                <div class="tab-pane {{ \$isTabInfo }}">
                    <div style="width: 100% !important;">
                        @yield( 'tab-info' )
                    </div>
                </div>
                @endpermission
            </div>
        </div>
    </div>
@endsection
EOD;

$setup['_actions']['alias'] = 'a';
$setup['_actions']['name'] = 'actions.blade';
$setup['_actions']['namespace'] = "resources/views/{$entitySlugName}";
$setup['_actions']['tpl'] = <<<EOD
@php use App\Constants\PermissionSlug;
/** @var {$setup['entity']['namespace']}\\{$setup['entity']['name']} \$entity */
@endphp

@permission(PermissionSlug::read(PermissionSlug::{$entityPermissionName}))
@include('partials.read_button', ['route' => route( '{$entitySlugName}.show', \$entity->id() ), 'small' => true])
@endpermission

@permission(PermissionSlug::edit(PermissionSlug::{$entityPermissionName}))
@include('partials.edit_button', ['route' => route( '{$entitySlugName}.edit', \$entity->id() ), 'small' => true])
@endpermission

@permission(PermissionSlug::delete(PermissionSlug::{$entityPermissionName}))
@if(!\$entity->haveInformationRelated())
    @include('partials.delete_button', ['route' => route( '{$entitySlugName}.destroy', \$entity->id() ), 'small' => true])
@endif
@endpermission
EOD;

$setup['_lang']['alias'] = 'l';
$setup['_lang']['name'] = $entitySnakeName;
$setup['_lang']['namespace'] = "resources/lang/en-US";
$setup['_lang']['tpl'] = <<<EOD
<?php

return [
    'title' => [
        'entity' => '{$modelName}|$modelNamePlural',
    ],

    'columns' => [
        {$entityColumns}
    ],
];

EOD;

function getPath($path)
{
    static $pathReady = [];

    if (!isset($pathReady[$path])) {
        $folders = explode('\\', $path);

        $parts = [];
        foreach ($folders as $folder) {
            $parts[] = $folder == 'App' ? strtolower($folder) : $folder;
        }

        $pathReady[$path] = implode(DIRECTORY_SEPARATOR, $parts);
    }

    return $pathReady[$path];
}

function snakeCase($string, $char = '-')
{
    return strtolower(preg_replace('/(?<!^)[A-Z]/', $char . '$0', $string));

}

function pascalCase($string, $lowerFirst = true, $find = '_', $replace = '')
{
    $pascal = str_replace($find, $replace, ucwords($string, $find));
    return $lowerFirst ? lcfirst($pascal) : $pascal;
}

function propertyIsNumeric($name)
{
    return substr($name, -3) === '_id'
        || strpos($name, 'value') !== false
        || strpos($name, 'number') !== false
        || strpos($name, 'size') !== false
        || strpos($name, 'have_information_related') !== false
        || strpos($name, 'total') !== false
        || strpos($name, 'amount') !== false
        || strpos($name, 'tax') !== false;
}


foreach ($setup as $type => $params) {

    /** @var $alias */
    /** @var $name */
    /** @var $namespace */
    /** @var $tpl */
    extract($params);

    if (empty($aliasToCreate)) {
        echo PHP_EOL . "{$alias} \t [$type]";
        continue;
    }

    $path = getPath($namespace);
    $folder = $pathApp . DIRECTORY_SEPARATOR . $path;
    $file = $folder . DIRECTORY_SEPARATOR . $name . '.php';
    $createFile = strpos($aliasToCreate, $alias) !== false && isset($tpl);

    if ($createFile && !file_exists($file)) {
        if (!is_dir($folder)) {
            echo PHP_EOL . 'Creating folder: ' . $folder;
            if (!mkdir($folder, 0755)) {
                die('Error');
            }

            chown($file, $user);
            chgrp($file, $group);
        }

        echo PHP_EOL . 'Creating file: ' . $file;
        if (file_put_contents($file, $tpl) === false) {
            die('Error');
        }

        chmod($file, 0644);
        chown($file, $user);
        chgrp($file, $group);

        echo PHP_EOL . "Ready {$modelName}'s $type \t\t [$file]";
    } elseif ($createFile) {
        echo PHP_EOL . $file . " exists this is new content:";
        echo PHP_EOL . '------------------------------------------------';
        echo PHP_EOL;
        echo $tpl;
        echo PHP_EOL;
        echo PHP_EOL . '------------------------------------------------';
    }
}