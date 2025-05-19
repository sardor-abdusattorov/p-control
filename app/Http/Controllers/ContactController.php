<?php

namespace App\Http\Controllers;

use App\Http\Requests\Contacts\ContactIndexRequest;
use App\Models\Contact;
use App\Models\ContactCategory;
use App\Models\ContactSubcategory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ContactController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->user() || !auth()->user()->can('manage contacts')) {
                return redirect()->route('dashboard')->with('error', __('app.deny_access'));
            }
            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     */
    public function index(ContactIndexRequest $request)
    {
        $contacts = Contact::query();
        if ($request->has('search')) {
            $contacts->where('name', 'LIKE', "%" . $request->search . "%");
        }
        if ($request->has(['field', 'order'])) {
            $contacts->orderBy($request->field, $request->order);
        }
        $perPage = $request->has('perPage') ? $request->perPage : 10;

        return Inertia::render('Contacts/Index', [
            'title'       => __('app.label.contacts'),
            'filters'     => $request->all(['search', 'field', 'order']),
            'perPage'     => (int) $perPage,
            'contacts'    => $contacts->paginate($perPage)->withQueryString(),
            'breadcrumbs' => [['label' => __('app.label.contacts'), 'href' => route('contacts.index')]],
            'categories'  => ContactCategory::all(),
            'subcategories' => ContactSubcategory::all(),
        ]);

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
