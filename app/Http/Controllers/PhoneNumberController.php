<?php

namespace App\Http\Controllers;

use App\Models\Photo;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\PhoneNumber;
use Illuminate\Support\Facades\Gate;

class PhoneNumberController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $phoneNumbers = PhoneNumber::with('user')
            ->where('user_id', auth()->id())
            ->orWhereJsonContains('shared_user_ids', (string)auth()->id())->get();
        return view('phonenumbers.index', compact('phoneNumbers'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('phonenumbers.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'=>'required',
            'phone-number'=>'required',
        ]);

        $phoneNumber = new PhoneNumber([
            'name' => $request->get('name'),
            'phonenumber' => $request->get('phone-number'),
            'user_id' => auth()->id()
        ]);
        $phoneNumber->save();

        if ($request->hasFile('file')) {

            $request->validate([
                'image' => 'mimes:jpeg,bmp,png'
            ]);

            $request->file->store('photo', 'public');

            $product = new Photo([
                "user_id" => $phoneNumber->id,
                "file_path" => $request->file->hashName()
            ]);
            $product->save(); // Finally, save the record.
        }
        return redirect('/phone-numbers')->with('success', 'Phone number saved!');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $phoneNumber = PhoneNumber::find($id);
        if (! Gate::allows('view-phone-number', $phoneNumber)) {
            abort(403);
        }
        $phoneNumber = PhoneNumber::findOrFail($id);
        $users = User::where('id', '!=', auth()->id())->get();
        \QrCode::size(500)
            ->format('png')
            ->generate($phoneNumber->name.':'.$phoneNumber->phoneNumber, public_path('images/qrcode.png'));
        $photo = Photo::where('user_id', $phoneNumber->id)->first();
        if(isset($photo->file_path))
        $photo = $photo->file_path;
        return view('phonenumbers.show', compact('phoneNumber', 'users', 'photo'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $phoneNumber = PhoneNumber::findOrFail($id);
        if (! Gate::allows('update-phone-number', $phoneNumber)) {
            abort(403);
        }
        return view('phonenumbers.update', compact('phoneNumber'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $phoneNumber = PhoneNumber::find($id);
        if (! Gate::allows('update-phone-number', $phoneNumber)) {
            abort(403);
        }
        $data = $request->validate([
            'name' => 'required|max:255',
            'phonenumber' => 'required|numeric',
        ]);

        PhoneNumber::where('id', $id)->update($data);
        return redirect('/phone-numbers')->with('success', 'Phone number updated');
    }


    public function share($id)
    {
        $phoneNumber = PhoneNumber::find($id);
        if (! Gate::allows('share-phone-number', $phoneNumber)) {
            abort(403);
        }
        $phoneNumber = PhoneNumber::findOrFail($id);
        $users= User::where('id', '!=', auth()->id())->get();
        return view('phonenumbers.share', compact('phoneNumber', 'users'));
    }

    public function makeShare(Request $request, $id)
    {
        $userIDs = $request->input('user-id');
        $phoneNumber = PhoneNumber::find($id);
        $phoneNumber->shared_user_ids = $userIDs;
        $phoneNumber->save();
        return redirect('/phone-numbers')->with('success', 'Phone number shared');
        //$phoneNumber = PhoneNumber::findOrFail($id);
        //return view('phoneNumbers.makeShare', compact('phoneNumber'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $phoneNumber = PhoneNumber::findOrFail($id);
        $phoneNumber->delete();

        return redirect('/phone-numbers')->with('success', 'Phone number deleted');
    }
}
