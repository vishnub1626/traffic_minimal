<?php

class UserController extends \BaseController {

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $users = User::all();
        return Response::json(array($users), 200);
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        //
    }


    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {
        $firstname = Request::get('firstname');
        $lastname  = Request::get('lastname');
        $username  = Request::get('username');
        $password  = Hash::make(Request::get('password'));
        $email     = Request::get('email');
        $mobile    = Request::get('mobile');

        $validation = Validator::make(Input::all(), User::$rules, User::$messages);

        if($validation->passes()){
            $code = md5($username.time());
            $user = new User;
            $user->firstname = $firstname;
            $user->lastname = $lastname;
            $user->username = $username;
            $user->password = $password;
            $user->email = $email;
            $user->mobile = $mobile;
            $user->confirmation_code = $code;
            $user->save();

            Mail::send('users.mails.verify', array('name'=> $firstname . ' ' . $lastname, 'code'=>$code), function($message){
                $message->to(Request::get('email'))->subject('Activate your Technowell Traffic account!');
            });
            return Response::json(array(
                'status' => 'OK',
                'user' => $user
            ), 200);
        }
        return Response::json(array(
                'status' => 'FAILED',
                'errors' =>array(
                    'firstname' => $validation->messages()->first('firstname'),
                    'username' => $validation->messages()->first('username'),
                    'email' => $validation->messages()->first('email'),
                    'mobile' => $validation->messages()->first('mobile'))

                ));
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        //
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        //
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id)
    {
        //
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }

    // Verifies the users email

    public function verify($confirmation_code)
    {
        $user = User::where('confirmation_code', '=', $confirmation_code)->first();
        if($user){
            $user->active = 1;
            $user->confirmation_code = null;
            $user->save();
            return View::make('users.verified')->withUser($user->firstname);
        }

        return View::make('users.verified')->withMessage('Not there');
    }

    //Login a user

    public function login()
    {
        $username = Request::get('username');
        $password = Request::get('password');
        $device_id = Request::get('device_id');

        $field = filter_var($username, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $user = array(
            $field => $username,
            'password' => $password,
            'active' => 1
            );

        if(Auth::attempt($user)) {
            $device = RegisteredDevice::where('device_id', '=', $device_id)->first();
            $device->username = $username;
            $device->save();

            return Response::json(array(
                "status" => 'OK'),
                200
                );
        }

        return Response::json(array(
                "status" => 'FAILED'),
                200);
    }

    //Change user password

    public function changePassword()
    {
        $username = Request::get('username');
        $password = Request::get('password');
        $new_password = Request::get('new_password');

        $validation = Validator::make(Input::all(), User::$changePasswordRules, User::$changePasswordMessages);
        if($validation->fails())
        {
            return Response::json(array(
                'status' => 'FAILED',
                'errors' =>array(
                    'username' => $validation->messages()->first('username'),
                    'password' => $validation->messages()->first('password'),
                    'new_password' => $validation->messages()->first('new_password'))

                ));
        }
        else
        {
            $field = filter_var($username, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
            $user = array(
                $field => $username,
                'password' => $password
                );

            if(Auth::attempt($user)) {
                $user_id = User::where('username', '=', $username)->first();
                $user_id->password = Hash::make($new_password);
                $user_id->save();

                return Response::json(array(
                    "status" => 'OK'),
                    200
                    );
            }
            else
            {
                return Response::json(array(
                'status' => 'FAILED',
                'errors' =>array(
                    'password' => 'No match found for username and password. Try again.')

                ));
            }
        }
    }

    //Change mobile number

    public function changeMobileNUmber()
    {
        $username = Request::get('username');
        $password = Request::get('password');
        $mobile = Request::get('mobile');
        $new_mobile = Request::get('new_mobile');

        $validation = Validator::make(Input::all(), User::$changeMobileRules, User::$changeMobileMessages);
        if($validation->fails())
        {
            return Response::json(array(
                'status' => 'FAILED',
                'errors' =>array(
                    'username' => $validation->messages()->first('username'),
                    'password' => $validation->messages()->first('password'),
                    'mobile' => $validation->messages()->first('mobile'),
                    'new_mobile' => $validation->messages()->first('new_mobile'),
                    )

                ));
        }
        else
        {
            $field = filter_var($username, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
            $user = array(
                $field => $username,
                'password' => $password
                );

            if(Auth::attempt($user)) {
                $user_id = User::where('username', '=', $username)->first();
                $user_id->mobile = $new_mobile;
                $user_id->save();

                return Response::json(array(
                    "status" => 'OK'),
                    200
                    );
            }
            else
            {
                return Response::json(array(
                'status' => 'FAILED',
                'errors' =>array(
                    'password' => 'No match found for username and password. Try again.')

                ));
            }
        }
    }

    //Reset password

    public function forgotPassword()
    {
        $password = rand(100000, 2000000);
        $email = Request::get('email');
        $user = User::where('email', '=', $email)->first();
        print_r($user);
        if($user) {
            $user->password = Hash::make($password);
            $user->save();
        }
        Mail::send('users.mails.welcome', array('firstname'=>'Technowell Traffic User', 'password'=>$password), function($message){
            $message->to(Request::get('email'))->subject('Welcome to the Laravel 4 Auth App!');
        });
    }
}
