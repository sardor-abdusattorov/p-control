<?php

namespace App\Http\Controllers;

use App\Http\Requests\Contacts\ContactIndexRequest;
use App\Http\Requests\Contacts\ContactStoreRequest;
use App\Http\Requests\Contacts\ContactUpdateRequest;
use App\Models\Contact;
use App\Models\ContactCategory;
use App\Models\ContactSubcategory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Altwaireb\World\Models\Country;
use Altwaireb\World\Models\City;

class ContactController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:create contact', ['only' => ['create', 'store', 'findByEmail', 'getCities', 'getSubcategories', 'storeModal']]);
        $this->middleware('permission:read contact', ['only' => ['index', 'show']]);
        $this->middleware('permission:update contact', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete contact', ['only' => ['destroy', 'destroyBulk']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(ContactIndexRequest $request)
    {
        $contacts = Contact::query()
            ->with(['country', 'owner', 'category']);

        if ($request->filled('title')) {
            $contacts->where('title', 'like', '%' . $request->title . '%');
        }

        if ($request->filled('email')) {
            $contacts->where('email', 'like', '%' . $request->email . '%');
        }

        if ($request->filled('category_id')) {
            $contacts->where('category_id', $request->category_id);
        }

        if ($request->filled('country')) {
            $contacts->where('country', $request->country);
        }

        if ($request->filled('owner_id')) {
            $contacts->where('owner_id', $request->owner_id);
        }

        if ($request->filled('field') && $request->filled('order')) {
            $contacts->orderBy($request->field, $request->order);
        } else {
            $contacts->latest();
        }

        $perPage = $request->get('perPage', 10);

        return Inertia::render('Contacts/Index', [
            'title'        => __('app.label.contacts'),
            'filters'      => $request->only(['title', 'email', 'category_id', 'country', 'city', 'owner_id', 'field', 'order', 'perPage']),
            'perPage'      => (int) $perPage,
            'contacts'     => $contacts->paginate($perPage)->withQueryString(),
            'categories' => ContactCategory::where('status', 1)->get(),
            'countries' => Country::select('id', 'name')->orderBy('name')->get(),
            'users' => User::where('status', 1)->select('id', 'name')->get(),
            'breadcrumbs'  => [['label' => __('app.label.contacts'), 'href' => route('contacts.index')]],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Contacts/Create', [
            'title' => __('app.label.contacts'),
            'breadcrumbs' => [
                ['label' => __('app.label.contacts'), 'href' => route('contacts.index')],
                ['label' => __('app.label.create')],
            ],
            'categories'      => ContactCategory::where('status', 1)->get(),
            'subCategories'   => ContactSubcategory::where('status', 1)->get(),
            'countries'       => Country::select('id', 'name')->orderBy('name')->get(),
            'statuses' => Contact::getStatuses(),
        ]);
    }

    public function findByEmail(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $contact = Contact::where('email', $request->email)
            ->where('owner_id', auth()->id())
            ->where('status', 1)
            ->first();

        if (!$contact) {
            return response()->json(null);
        }

        return response()->json($contact);
    }


    public function getCities(Request $request)
    {
        $countryId = $request->input('country');

        if (!$countryId) {
            return response()->json([]);
        }

        return City::where('country_id', $countryId)
            ->where('is_active', 1)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function getSubcategories(Request $request)
    {
        $categoryId = $request->get('category_id');

        if (!$categoryId) {
            return response()->json([]);
        }

        return ContactSubcategory::query()
            ->where('category_id', $categoryId)
            ->orderBy('title')
            ->get(['id', 'title']);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ContactStoreRequest $request)
    {
        DB::beginTransaction();

        try {
            $contact = new Contact();

            $contact->title          = $request->input('title');
            $contact->prefix      = $request->input('prefix');
            $contact->firstname      = $request->input('firstname');
            $contact->lastname       = $request->input('lastname');
            $contact->phone          = $request->input('phone');
            $contact->cellphone      = $request->input('cellphone');
            $contact->email          = $request->input('email');
            $contact->company        = $request->input('company');
            $contact->language       = $request->input('language');
            $contact->country        = $request->input('country');
            $contact->city           = $request->input('city');
            $contact->post_box       = $request->input('post_box');
            $contact->zip_code       = $request->input('zip_code');
            $contact->address        = $request->input('address');
            $contact->address2       = $request->input('address2');
            $contact->category_id    = $request->input('category_id');
            $contact->subcategory_id = $request->input('subcategory_id');
            $contact->status         = $request->input('status');
            $contact->owner_id       = auth()->id();

            $contact->save();

            DB::commit();

            return redirect()->route('contacts.index')
                ->with('success', __('app.label.created_successfully', ['name' => $contact->title]));
        } catch (\Throwable $th) {
            DB::rollback();
            return back()->withInput()
                ->with('error', __('app.label.created_error', ['name' => __('app.label.contacts')]) . ' ' . $th->getMessage());
        }
    }

    public function storeModal(ContactStoreRequest $request)
    {
        DB::beginTransaction();

        try {
            $contact = new Contact();

            $contact->title          = $request->input('title');
            $contact->prefix         = $request->input('prefix');
            $contact->firstname      = $request->input('firstname');
            $contact->lastname       = $request->input('lastname');
            $contact->phone          = $request->input('phone');
            $contact->cellphone      = $request->input('cellphone');
            $contact->email          = $request->input('email');
            $contact->company        = $request->input('company');
            $contact->language       = $request->input('language');
            $contact->country        = $request->input('country');
            $contact->city           = $request->input('city');
            $contact->post_box       = $request->input('post_box');
            $contact->zip_code       = $request->input('zip_code');
            $contact->address        = $request->input('address');
            $contact->address2       = $request->input('address2');
            $contact->category_id    = $request->input('category_id');
            $contact->subcategory_id = $request->input('subcategory_id');
            $contact->status         = Contact::STATUS_ACTIVE;
            $contact->owner_id       = auth()->id();

            $contact->save();

            DB::commit();

            return back()->with([
                'success' => __('app.label.created_successfully', ['name' => $contact->title]),
                'new_contact_id' => $contact->id,
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return back()->with([
                'error' => __('app.label.created_error', ['name' => __('app.label.contacts')]) . ' ' . $th->getMessage(),
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Contact $contact)
    {
        $category     = ContactCategory::find($contact->category_id);
        $subcategory  = ContactSubcategory::find($contact->subcategory_id);
        $owner        = User::find($contact->owner_id);
        $country      = Country::find($contact->country);
        $city         = City::find($contact->city);

        return Inertia::render('Contacts/Show', [
            'contact'     => $contact,
            'category'    => $category,
            'subcategory' => $subcategory,
            'owner'       => $owner,
            'country'     => $country,
            'city'        => $city,
            'title'       => __('app.label.contacts'),
            'breadcrumbs' => [
                ['label' => __('app.label.contacts'), 'href' => route('contacts.index')],
                ['label' => $contact->title ?? $contact->firstname]
            ]
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Contact $contact)
    {
        return inertia('Contacts/Edit', [
            'contact' => $contact,
            'title'       => __('app.label.contact_subcategories'),
            'breadcrumbs' => [
                ['label' => __('app.label.contacts'), 'href' => route('contacts.index')],
                ['label' => $contact->id, 'href' => route('contacts.show', $contact->id)],
                ['label' => __('app.label.edit')]
            ],
            'categories'      => ContactCategory::where('status', 1)->get(),
            'subCategories'   => ContactSubcategory::where('status', 1)->get(),
            'countries'       => Country::select('id', 'name')->orderBy('name')->get(),
            'statuses' => Contact::getStatuses(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ContactUpdateRequest $request, Contact $contact)
    {
        DB::beginTransaction();

        try {
            $contact->update([
                'prefix'          => $request->prefix,
                'firstname'       => $request->firstname,
                'lastname'        => $request->lastname,
                'title'           => $request->title,
                'company'         => $request->company,
                'phone'           => $request->phone,
                'cellphone'       => $request->cellphone,
                'email'           => $request->email,
                'language'        => $request->language,
                'country'         => $request->country,
                'city'            => $request->city,
                'post_box'        => $request->post_box,
                'zip_code'        => $request->zip_code,
                'address'         => $request->address,
                'address2'        => $request->address2,
                'category_id'     => $request->category_id,
                'subcategory_id'  => $request->subcategory_id,
                'status'          => $request->status,
            ]);

            DB::commit();
            return redirect()->route('contacts.index')->with('success', __('app.label.updated_successfully', ['name' => $contact->title]));
        } catch (\Throwable $th) {
            DB::rollBack();
            return back()->with('error', __('app.label.updated_error', ['name' => $contact->title]) . ' ' . $th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Contact $contact)
    {
        DB::beginTransaction();
        try {
            $contact->delete();
            DB::commit();
            return redirect()->route('contacts.index')->with('success', __('app.label.deleted_successfully', ['name' => $contact->title]));
        } catch (\Throwable $th) {
            DB::rollback();
            return back()->with('error', __('app.label.deleted_error', ['name' => $contact->title]) . $th->getMessage());
        }
    }

    public function destroyBulk(Request $request)
    {
        try {
            $contact = Contact::whereIn('id', $request->id);
            $contact->delete();
            return back()->with('success', __('app.label.deleted_successfully', ['name' => count($request->id) . ' ' . __('app.label.contacts')]));
        } catch (\Throwable $th) {
            return back()->with('error', __('app.label.deleted_error', ['name' => count($request->id) . ' ' . __('app.label.contacts')]) . $th->getMessage());
        }
    }
}
