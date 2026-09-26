<?php
namespace App\Console\Commands;
use App\Models\User; use Illuminate\Console\Command; use Illuminate\Support\Facades\Hash; use Illuminate\Support\Facades\Validator;
class CreateSuperAdmin extends Command { protected $signature='admin:create-super'; protected $description='Create first super administrator';
 public function handle(): int { if(User::where('role','SUPER_ADMIN')->exists()){ $this->error('Super admin already exists; nothing changed.'); return self::FAILURE; }
 $name=$this->ask('Name'); $email=$this->ask('Email'); $password=$this->secret('Password (minimum 12 characters)');
 $v=Validator::make(compact('name','email','password'),['name'=>'required|string|max:255','email'=>['required','email','max:255','unique:users,email','not_regex:/[\r\n]/'],'password'=>'required|string|min:12']);
 if($v->fails()){ foreach($v->errors()->all() as $e)$this->error($e); return self::INVALID; }
 User::create(['name'=>$name,'email'=>$email,'password'=>Hash::make($password),'role'=>'SUPER_ADMIN','is_active'=>true]); $this->info('Super admin created.'); return self::SUCCESS; }
}
