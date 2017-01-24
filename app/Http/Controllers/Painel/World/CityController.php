<?php

namespace App\Http\Controllers\Painel\World;

use \App\Http\Controllers\Controller;
use App\Interest;
use Illuminate\Support\Facades\DB;
use App\City;
use App\Estate;

class CityController extends Controller {

    use \App\Library\Jobs;

    protected $model;
    public $city;
    public $cityId;
    public $interests;
    public $allInterests;

    public function __construct($cityId)
    {
        $arr = [
            'id',
            'name',
            'estates_id',
            'status',
            'comments',
            'search_tags',
            'seo_tags',
            'created_at',
            'updated_at',
            'cities_photos_id',
            'lat',
            'lng',
        ];

        $this->model    = new City();
        $this->cityId   = $cityId;
        $this->city     = $this->model->where('id', $cityId)->get($arr);
    }

    function display()
    {
        if ( isset($this->city[0]) ) {
            $this->city = $this->city[0]->getAttributes();
        }else{
            trigger_error('Esta cidade ainda não está cadastrada no banco. Favor gerar um Blog/Post para depois editar suas configurações', E_USER_ERROR);
        }

        /*
         * Isso porque o CITY.JS é executado tanto no SITE para edição do post, quanto no painel para edição de
         * cofigurações, se for painel, o script CITY inicial de forma diferenciada.
         * */
        $this->json_meta(['isPainel' => true]);

        $this->interests();
        $this->vars['city'] = $this->city;
        $this->vars['modulo'] = 'Cidade';
        $this->vars['pageDesc'] = 'Configurações da cidade: '.$this->city['name'];

        return view('Painel.world.city', $this->vars);
    }

    public function interests()
    {
        $this->interests = DB::table('city_has_interests as many')
            ->select('it.color', 'many.interest_id', 'it.name')
            ->join('interests as it', 'it.id', 'many.interest_id')
            ->where('many.city_id', $this->city['id'])
            ->get()
        ;

        $selecteds = [];
        foreach ($this->interests as $selected)
        {
            array_push($selecteds, $selected->interest_id);
        }

        $this->allInterests = Interest::all();
        foreach ($this->allInterests as &$all)
        {
            $all['checked'] = (array_search($all->id, $selecteds) !== false) ? 'checked="checked"' : '';
        }

        $this->vars['interests'] = $this->interests;
        $this->vars['allInterests'] = $this->allInterests;
    }

    function createAjaxAction($request)
    {
        $data = $request['screen_json'];
        $html = $request['html'];

        $country    = $data['country'];
        $estate     = $data['estate'];
        $city       = $data['city'];

        $hasEstate = Estate::where('id', $estate['geonameId'])->get()[0] ?? false;
        if ( !$hasEstate ) {
            $e = new Estate();

            $e->id              = $estate['geonameId'];
            $e->name            = $estate['name'];
            $e->ll_north        = $estate['bbox']['north'];
            $e->ll_south        = $estate['bbox']['south'];
            $e->ll_east         = $estate['bbox']['east'];
            $e->ll_west         = $estate['bbox']['west'];
            $e->lat             = ($estate['bbox']['north']+$estate['bbox']['south'])/2;
            $e->lng             = ($estate['bbox']['east']+$estate['bbox']['west'])/2;
            $e->countries_id    = $country['id'];

            $e->save();
        }

        $this->model->id            = $city['geonameId'];
        $this->model->name          = $city['name'];
        $this->model->estates_id    = $estate['geonameId'];
        $this->model->ll_north      = $city['bbox']['north'];
        $this->model->ll_south      = $city['bbox']['south'];
        $this->model->ll_east       = $city['bbox']['east'];
        $this->model->ll_west       = $city['bbox']['west'];
        $this->model->lat           = ($city['bbox']['north']+$city['bbox']['south'])/2;
        $this->model->lng           = ($city['bbox']['east']+$city['bbox']['west'])/2;
        $this->model->content       = $html;

        $this->model->save();

        $res = [
            'status' => true
        ];

        return json_encode($res);
    }

    function updateAjaxAction($request)
    {
        $city = $this->model->find($request->screen_json['city_id']);
        $city->content = $request['html'];
        $city->save();

        $res = [
            'status' => true
        ];

        return json_encode($res);
    }

    function activateAjaxAction($request)
    {
        $city = $this->model->find($request['screen_json']['city']['geonameId']);
        $city->status = 1;
        $city->save();

        $res = [
            'status' => true,
            'ascii_name' => $this->toAscii($city->name)
        ];

        return json_encode($res);
    }

    public function updateInterestsAjaxAction($request)
    {
        $table = 'city_has_interests';
        $interests = $request->ints;

        DB::table($table)->where('city_id', $this->cityId)->delete();

        foreach ($interests as $in){
            DB::table($table)->insert(
                [
                    'city_id' => $this->cityId,
                    'interest_id' => $in,
                ]
            );
        }

        return json_encode(['status' => true]);
    }
}
