<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Country;
use App\Models\Province;
use App\Models\District;
use App\Models\Sector;
use App\Models\Cell;
use App\Models\Village;

class AdminLocationController extends Controller
{
    protected $levels = [
        'country'  => ['model' => Country::class,  'parent_field' => null,          'parent_level' => null],
        'province' => ['model' => Province::class, 'parent_field' => 'country_id',  'parent_level' => 'country'],
        'district' => ['model' => District::class, 'parent_field' => 'province_id', 'parent_level' => 'province'],
        'sector'   => ['model' => Sector::class,   'parent_field' => 'district_id', 'parent_level' => 'district'],
        
         ];

    protected function normalizeLevel($level)
    {
        $level = strtolower($level ?? 'country');
        return array_key_exists($level, $this->levels) ? $level : 'country';
    }

    public function index(Request $request)
    {
        $level = $this->normalizeLevel($request->query('level', 'country'));
        $model = $this->levels[$level]['model'];

        // eager-load parent where appropriate
        $with = [];
        if ($level === 'province') $with = ['country'];
        if ($level === 'district') $with = ['province'];
        if ($level === 'sector') $with = ['district'];

        $items = $model::with($with)->orderBy('name')->paginate(20)->withQueryString();

        $parents = $this->getParentOptions($level);

        return view('admin.locations.index', compact('level', 'items', 'parents'));
    }

    public function create(Request $request, $level = null)
{
    $level = $this->normalizeLevel($level);
    $parents = $this->getParentOptions($level);

    // Preselected parents via querystring so view page can pass them
    $preselect = [
        'country_id' => $request->query('country_id'),
        'province_id' => $request->query('province_id'),
        'district_id' => $request->query('district_id'),
        'sector_id' => $request->query('sector_id'),
        'cell_id' => $request->query('cell_id'),
    ];

    // ✅ Debugging log
    \Log::info("[AdminLocationController@create] level={$level}", $preselect);

    return view('admin.locations.create', compact('level','parents','preselect'));
}


public function store(Request $request)
{
    $level = $this->normalizeLevel($request->input('level'));
    $config = $this->levels[$level];
    $modelClass = $config['model'];

    $rules = ['name' => 'required|string|max:191'];

    // Require parent if not country
    if ($config['parent_field']) {
        $rules[$config['parent_field']] = 'required|integer|exists:' .
            $this->tableNameForParent($config['parent_level']) . ',id';
    }

    // Debug incoming
    \Log::info("[AdminLocationController@store] Incoming {$level} data:", $request->all());

    $validated = $request->validate($rules);

    // Debug validated
    \Log::info("[AdminLocationController@store] Validated {$level} data:", $validated);

    // Ensure unique name within same parent
    $uniqueQuery = $modelClass::where('name', $validated['name']);
    if ($config['parent_field']) {
        $uniqueQuery->where($config['parent_field'], $validated[$config['parent_field']]);
    }

    if ($uniqueQuery->exists()) {
        \Log::warning("[AdminLocationController@store] Duplicate {$level} detected:", $validated);
        return back()->withInput()->withErrors(['name' => 'This name already exists for the selected parent.']);
    }

    // Create record
    $item = $modelClass::create($validated);
    \Log::info("[AdminLocationController@store] {$level} created successfully:", $item->toArray());

    // --- NEW: Auto-fill full parent chain ---
    $redirectParams = [];

    switch ($level) {
        case 'province':
            $redirectParams = [
                'country_id' => $validated['country_id'] ?? null,
            ];
            break;

        case 'district':
            $province = \App\Models\Province::find($validated['province_id']);
            $redirectParams = [
                'country_id' => $province?->country_id,
                'province_id' => $province?->id,
            ];
            break;

        case 'sector':
            $district = \App\Models\District::find($validated['district_id']);
            $redirectParams = [
                'country_id' => $district?->province?->country_id,
                'province_id' => $district?->province_id,
                'district_id' => $district?->id,
            ];
            break;

    }

    // Log redirect info
    \Log::info("[AdminLocationController@store] Redirecting back to create with full chain:", [
        'level' => $level,
        'params' => $redirectParams,
    ]);

    // Redirect logic
    if ($level !== 'country') {
        return redirect()
            ->route('admin.locations.create', array_merge(['level' => $level, 'success' => 1], $redirectParams))
            ->with('success', ucfirst($level) . ' created successfully. You can add another one.');
    }

    // Country → go to index
    return redirect()
        ->route('admin.locations.index', ['level' => $level])
        ->with('success', ucfirst($level) . ' created successfully.');
}






  public function edit($level, $id)
{
    $level = $this->normalizeLevel($level);
    $config = $this->levels[$level];
    $modelClass = $config['model'];

    // Force-load all necessary relationships
    $with = [];
    switch ($level) {
        case 'province':
            $with = ['country'];
            break;
        case 'district':
            $with = ['province.country'];
            break;
        case 'sector':
            $with = ['district.province.country'];
            break;
    }

    $item = $modelClass::with($with)->findOrFail($id);

    // 🔍 Build the full parent chain
    $parentChain = [
        'country_id' => null,
        'province_id' => null,
        'district_id' => null,
        'sector_id' => null,
    ];

    switch ($level) {
        case 'country':
            $parentChain['country_id'] = $item->id;
            break;

        case 'province':
            $parentChain['province_id'] = $item->id;
            $parentChain['country_id'] = $item->country_id;
            break;

        case 'district':
            $parentChain['district_id'] = $item->id;
            $parentChain['province_id'] = $item->province_id;
            $parentChain['country_id'] = optional($item->province)->country_id;
            break;

        case 'sector':
            $parentChain['sector_id'] = $item->id;
            $parentChain['district_id'] = $item->district_id;
            $parentChain['province_id'] = optional($item->district)->province_id;
            $parentChain['country_id'] = optional(optional($item->district)->province)->country_id;
            break;
    }

    \Log::info("[AdminLocationController@edit] level={$level}", $parentChain);

    return view('admin.locations.edit', compact('level', 'item', 'parentChain'));
}





    public function update(Request $request, $level, $id)
    {
        $level = $this->normalizeLevel($level);
        $config = $this->levels[$level];
        $modelClass = $config['model'];

        $item = $modelClass::findOrFail($id);

        $rules = ['name' => 'required|string|max:191'];
        if ($config['parent_field']) {
            $rules[$config['parent_field']] = 'required|integer|exists:' . $this->tableNameForParent($config['parent_level']) . ',id';
        }

        $validated = $request->validate($rules);

        // uniqueness check
        $uniqueQuery = $modelClass::where('name', $validated['name'])->where('id', '!=', $item->id);
        if ($config['parent_field']) {
            $uniqueQuery->where($config['parent_field'], $validated[$config['parent_field']]);
        }
        if ($uniqueQuery->exists()) {
            return back()->withInput()->withErrors(['name' => 'This name already exists for the selected parent.']);
        }

        $item->update($validated);

        return redirect()->route('admin.locations.index', ['level' => $level])->with('success', ucfirst($level) . ' updated.');
    }

    public function destroy($level, $id)
    {
        $level = $this->normalizeLevel($level);
        $config = $this->levels[$level];
        $modelClass = $config['model'];

        $item = $modelClass::findOrFail($id);
        try {
            $item->delete();
            return redirect()->back()->with('success', ucfirst($level) . ' deleted.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Cannot delete: ' . $e->getMessage());
        }
    }

    /**
     * New: view full hierarchy for given level+id.
     * Example: view('country', 1) shows that country and all nested children.
     */
    public function view($level, $id)
    {
        $level = $this->normalizeLevel($level);
        // If viewing a country -> load full nested tree via eager loading
        if ($level === 'country') {
            $country = Country::with('provinces.districts.sectors.cells.villages')->findOrFail($id);
            return view('admin.locations.view', compact('level','country'));
        }

        // If viewing province -> we still want the entire tree up and down:
        if ($level === 'province') {
            $province = Province::with('districts.sectors.cells.villages','country')->findOrFail($id);
            return view('admin.locations.view', compact('level','province'));
        }

        if ($level === 'district') {
            $district = District::with('sectors.cells.villages','province.country')->findOrFail($id);
            return view('admin.locations.view', compact('level','district'));
        }

        if ($level === 'sector') {
            $sector = Sector::with('cells.villages','district.province.country')->findOrFail($id);
            return view('admin.locations.view', compact('level','sector'));
        }

        

        abort(404);
    }

    // helper map for table name by level
    protected function tableNameForParent($level)
    {
        $map = [
            'country' => 'countries',
            'province' => 'provinces',
            'district' => 'districts',
            'sector' => 'sectors',
        ];
        return $map[$level] ?? 'countries';
    }

    // return parent collections needed for create/edit forms
    protected function getParentOptions($level, $item = null)
    {
        $config = $this->levels[$level];
        $res = [];

        // always return immediate parent collection only (for simplicity)
        if ($config['parent_level']) {
            $parentLevel = $config['parent_level'];
            $parentModel = $this->levels[$parentLevel]['model'];
            $res[$parentLevel] = $parentModel::orderBy('name')->get(['id','name']);
        }

        // For a sector/ we might need to return extra parents for select chains in blade
        // but blades already use API endpoints for cascading so returning only immediate parent is sufficient.
        return $res;
    }
}
