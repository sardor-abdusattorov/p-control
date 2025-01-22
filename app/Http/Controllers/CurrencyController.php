<?php

namespace App\Http\Controllers;

use App\Http\Requests\Currency\CurrencyIndexRequest;
use App\Http\Requests\Currency\CurrencyStoreRequest;
use App\Http\Requests\Currency\CurrencyUpdateRequest;
use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CurrencyController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->user() || !auth()->user()->can('manage currency')) {
                return redirect()->route('dashboard')->with('error', __('app.deny_access'));
            }
            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     */
    public function index(CurrencyIndexRequest $request)
    {
        $currencies = Currency::query();
        if ($request->has('search')) {
            $currencies->where('name', 'LIKE', "%" . $request->search . "%");
        }
        if ($request->has(['field', 'order'])) {
            $currencies->orderBy($request->field, $request->order);
        }
        $perPage = $request->has('perPage') ? $request->perPage : 10;

        return Inertia::render('Currency/Index', [
            'title'         => __('app.label.currencies'),
            'filters'       => $request->all(['search', 'field', 'order']),
            'perPage'       => (int) $perPage,
            'currencies'      => $currencies->paginate($perPage),
            'breadcrumbs'   => [['label' => __('app.label.currencies'), 'href' => route('currency.index')]],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Currency/Create', [
            'title' => __('app.label.currencies'),
            'breadcrumbs' => [
                ['label' => __('app.label.currencies'), 'href' => route('currency.index')],
                ['label' => __('app.label.create')],
            ],
            'statusOptions' => Currency::getStatuses(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CurrencyStoreRequest $request)
    {
        DB::beginTransaction();

        try {
            $currency = new Currency();
            $currency->name = $request->name;
            $currency->short_name = $request->short_name;
            $currency->value = $request->value ?? '1';
            $currency->status = $request->status;
            $currency->save();

            DB::commit();
            return redirect()->route('currency.index')->with('success', __('app.label.created_successfully', ['name' => $currency->name]));
        } catch (\Throwable $th) {
            DB::rollback();
            return back()->with('error', __('app.label.created_error', ['name' => __('app.label.currency')]) . ' ' . $th->getMessage());
        }
    }

    public function show(Currency $currency)
    {
        return Inertia::render('Currency/Show', [
            'currency' => $currency,
            'title' => __('app.label.currencies'),
            'breadcrumbs' => [
                ['label' => __('app.label.currency'), 'href' => route('currency.index')],
                ['label' => $currency->name]
            ],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Currency $currency)
    {
        return inertia('Currency/Edit', [
            'currency' => $currency,
            'title' => __('app.label.currencies'),
            'statusOptions' => Currency::getStatuses(),
            'breadcrumbs' => [
                ['label' => __('app.label.currency'), 'href' => route('currency.index')],
                ['label' => $currency->id, 'href' => route('currency.show', $currency->id)],
                ['label' => __('app.label.edit')]
            ]
        ]);
    }

    public function update(CurrencyUpdateRequest $request, Currency $currency)
    {
        DB::beginTransaction();
        try {
            $currency->update([
                'name' => $request->name,
                'short_name' => $request->short_name,
                'value' => $request->value,
                'status' => $request->status
            ]);

            DB::commit();
            return redirect()->route('currency.index')->with('success', __('app.label.updated_successfully', ['name' => $currency->name]));
        } catch (\Throwable $th) {
            DB::rollback();
            return back()->with('error', __('app.label.updated_error', ['name' => $currency->name]) . $th->getMessage());
        }
    }


    public function destroy(Currency $currency)
    {
        DB::beginTransaction();
        try {
            $currency->delete();
            DB::commit();
            return redirect()->route('currency.index')->with('success', __('app.label.deleted_successfully', ['name' => $currency->name]));
        } catch (\Throwable $th) {
            DB::rollback();
            return back()->with('error', __('app.label.deleted_error', ['name' => $currency->name]) . $th->getMessage());
        }
    }

    public function destroyBulk(Request $request)
    {
        try {
            $currency = Currency::whereIn('id', $request->id);
            $currency->delete();
            return back()->with('success', __('app.label.deleted_successfully', ['name' => count($request->id) . ' ' . __('app.label.currency')]));
        } catch (\Throwable $th) {
            return back()->with('error', __('app.label.deleted_error', ['name' => count($request->id) . ' ' . __('app.label.currency')]) . $th->getMessage());
        }
    }
}
