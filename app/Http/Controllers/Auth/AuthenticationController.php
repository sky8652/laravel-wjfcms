<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class AuthenticationController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Description:未授权跳转页
     * User: Vijay <1937832819@qq.com>
     * Date: 2019/11/29
     * Time: 18:36
     * @param $account
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function getSocialRedirect($account)
    {
        try {
            return Socialite::with($account)->redirect();
        } catch (\InvalidArgumentException $e) {
            return redirect('/register');
        }
    }

    /**
     * Description:OAuth 回调中获取用户信息
     * User: Vijay <1937832819@qq.com>
     * Date: 2019/11/29
     * Time: 18:36
     * @param $account
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function getSocialCallback($account)
    {
        $socialUser = Socialite::with($account)->user();
        // 在本地 users 表中查询该用户来判断是否已存在
        $user = User::where('provider_id', '=', $socialUser->id)
            ->where('provider', '=', $account)
            ->first();
        if ($user == null) {
            // 如果该用户不存在则将其保存到 users 表
            $newUser = new User();
            $newUser->name = $socialUser->getName();
            $newUser->email = $socialUser->getEmail() == '' ? '' : $socialUser->getEmail();
            $newUser->avatar = $socialUser->getAvatar();
            $newUser->password = Hash::make('123456');
            $newUser->provider = $account;
            $newUser->provider_id = $socialUser->getId();
            $newUser->save();
            $user = $newUser;
        }
        // 手动登录该用户
        Auth::login($user);
        // 登录成功后将用户重定向到首页
        return redirect('/');
    }
}