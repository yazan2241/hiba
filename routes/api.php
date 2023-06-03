<?php

use App\Models\donate;
use App\Models\Order;
use App\Models\person;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Response;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


// sign up
Route::Post('/signup', function (Request $request) {
    $name = $request->input('name');
    $phone_number = $request->input('phone');
    $email = $request->input('email');
    $password = $request->input('password');
   
    $person = new person();

    $person->name = $name;
    $person->phone = $phone_number;
    $person->email = $email;
    $person->password = $password;
    


    $item = Person::where('email', '=', $email)->first();
    if ($item) {
        return Response::json(
            ['error' => 'email already exist'],
            201
        );
    } else {
        if ($person->save()) {
            return Response::json(
                $person,
                200
            );
        } else {
            return Response::json(
                ['error' => 'error network'],
                202
            );
        }
    }
});
// log in
Route::Post('/login', function (Request $request) {
    $email = $request->input('email');
    $password = $request->input('password');

    $person = Person::where('email', '=', $email)->where('password', '=', $password)->first();
    if ($person) {
        return Response::json(
            $person,
            200
        );
    } else {
        return Response::json(
            ['error' => 'user not exist'],
            201
        );
    }
});
// reset password
Route::Post('/reset_password', function (Request $request , $id) {

    $person = person::find($id);
    $person->password =$request->input('new_password');
        if ($person->update()) {
            return Response::json(
                $person,
                200
            );
        } else {
            return Response::json(
                ['error' => 'error network'],
                201
            );
        }
});
//profile
Route::get('/profile', function (Request $request) {
    $id = $request->input('id');
    $person = person::find($id);
        if ($person) {
            return Response::json(
                $person,
                200
            );
        } else {
            return Response::json(
                ['error' => 'user not exist'],
                201
            );
        }
});

Route::Post('/editprofile', function (Request $request) {
    $id = $request->input('id');
    $user = person::find($id);
    if ($user) {
        
        $user->name = $request->input('name');
        $user->phone = $request->input('phone');
        $user->email = $request->input('email');
        $user->password = $request->input('password');
        $user->update();
        return Response::json($user, 200);
    } else {
        return Response::json(['error' => 'user not exist'], 201);
    }
});

//donate
Route::Post('/Donate', function (Request $request) {
    $donor_id = $request->input('donor_id');
    $item_name = $request->input('item_name');
    $item_image = $request->input('item_image');
    $item_address = $request->input('item_address');

    $donate = new donate();

    $donate->donor_id = $donor_id;
    $donate->item_name = $item_name;
    $donate->item_image = $item_image;
    $donate->item_address = $item_address;

    if ($donate->save()) {
        return Response::json(
            $donate,
            200
        );
    } else {
        return Response::json(
            ['error' => 'error network'],
            201
        );
    }
});


Route::post('/donations' , function(Request $request){
    $alldonations = donate::where('user_id' , '=' , 0)->get();
    return Response::json(
        $alldonations,
        200
    );
});

Route::post('/takeorder' , function(Request $request){
    $id = $request->input('id');

    $donations = donate::all();
    $taken = 0;
    foreach($donations as $donation){
        $date = $donation->updated_at;

        $today = date('Y-m-d H:i:s');

        $u_today = strtotime("-30 days" , $today);

        if($date > $u_today){
            if($donation->user_id == $id)
                $taken = 1;
        }

    }

    return Response::json(
        ['taken' => $taken],
        201
    );

});


Route::post('/addOrder' , function(Request $request){

    $order = new Order();
    $order->donation_id = $request->input('id');
    $order->user_id = $request->input('user_id');
    $order->name = $request->input('name');
    $order->address = $request->input('address');
    $order->desc = $request->input('description');

    if ($files = $request->file('images')) {
        $img = [];
        $i = 0;
        $order->image = '';
    
        foreach ($files as $file) {
            $imageName = time() . $file->getClientOriginalName();
            $file->move(public_path('images'), $imageName);

            $img[$i] = $imageName;
            $i = $i + 1;
        }
         foreach($img as $im){
             $order->image = $order->image . $im;
         }
    }

    if ( $order->save()) {
        return Response::json(
            $order,
            200
        );
    } else {
        return Response::json(
            ['error' => 'can not add the order'],
            201
        );
    }
});
