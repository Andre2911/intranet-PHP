<?php

namespace App\Http\Controllers\Auth;

use App\Persona;
use App\User;
use App\RoleUser;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;



class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/inicio';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }
    

    protected function registrar(Request $request)
    {
        $this->validator($request->all())->validate();

        $item = Persona::where('numero_documento','=',$request->n_doc)
                    ->with('tipo_documento')
                    ->first();

        if ($item == NULL){ 
            
            $form_data = array(
                'tipo_documento_id' => $request->tipo_doc,
                'numero_documento' => $request->n_doc,
                'ape_paterno' => mb_strtoupper($request->ap_paterno,'utf-8' ),
                'ape_materno' => mb_strtoupper($request->ap_materno,'utf-8' ),
                'nombre' => mb_strtoupper($request->nombres,'utf-8' ),
                'direccion' => mb_strtoupper($request->direccion,'utf-8' ),
                'celular' => $request->celular,
                'colegiatura' => $request->colegiatura,
                'casilla' => mb_strtoupper($request->casilla,'utf-8' ),
                'email'  => mb_strtolower($request->email),
                'estatus' => 1
            );

            $persona = Persona::create($form_data);

            event(new Registered($user = $this->create($request->all(), $persona)));

            $this->guard()->login($user);

            return $this->registered($request, $user)
            ?: redirect($this->redirectPath());
        }
        
    }


    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'nombres' => ['required', 'string', 'max:255'],
            'ap_paterno' => ['required', 'string', 'max:255'],
            'ap_materno' => ['required', 'string', 'max:255'],
            'n_doc' => ['required', 'numeric', 'min:8'],
            'colegiatura' => ['required', 'numeric', 'min:3'],
            'casilla' => ['required', 'string', 'min:3'],
            'celular' => ['required', 'string', 'min:9'],
            'digito' => ['required', "cod_verifica:".$data['n_doc'].",".$data['tipo_doc']],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'direccion' => ['required', 'string', 'max:500'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ],[
            'n_doc.min' => 'El N° de documento debe contener al menos 8 caracteres'
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data, $persona)
    {
        $reg = User::create([
            'cargo_id' => 3,
            'persona_id' => $persona->id,
            'username' => mb_strtolower($data['n_doc'],'utf-8' ),
            'password' => bcrypt($data['password']),
            'email' => $data['email'],
            'estado' => 1,
            //'password' => Hash::make($data['password']),
        ]);

        $form_data_2 = array(
            'role_id' => 3,
            'user_id' => $reg->id,
        );

        RoleUser::create($form_data_2);

        return $reg;

    }

    protected function guard()
    {
        return Auth::guard();
    }

    public function registered(Request $request, $user)
    {
        //
    }
}
