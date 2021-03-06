<?php

namespace App\Frontend\Controllers;

use App\Models\Frontend\User;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller {

    // 用于公开查看的用户主页
    public function userDetails(User $user) {
        /**
         * 需要传递的信息：用户名、头像、创作的文章数、关注数、粉丝数
         * 
         * 信息获取方法：
         * 通过User模型，使用withCount()在“不加载关联关系”的情况下统计关联结果数目，
         *  使用的前提是：在模型中提前定义/关联了要查找的模型；
         *    如下面的：['posts', 'stars', 'fans']，就是在User模型中定义的管理；
         *  withCount()只能在Query Builder（查询构建器）中使用,如在where、orderBy、find等语句之后调用。
         * find()查询与条件匹配的记录
         */
        $detailUser = User::withCount(['posts', 'fans', 'stars'])->find($user->id);

        /**
         * 创作的文章的列表，取出前十条
         * 
         * $user->posts()使用了模型绑定的访问方法（具体请查看User.php文件）
         */
        $posts = $user
                ->posts()
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get();

        /**
         * 该用户关注的用户的概况（包括用户名、创作的文章数、关注数、粉丝数、取消关注按钮）
         * 
         * whereIn() 验证给定列的值（对应下面的“id”）是否在给定数组中；
         * pluck() 对给定的键(字段)获取所有集合值；
         * withCount() 则在此查询构建器中执行统计。
         */
        $stars = $user->stars();
        $starUsers = User::whereIn('id', $stars->pluck('star_id'))
                ->withCount(['posts', 'fans', 'stars'])
                ->get();

        /**
         * 该用户的粉丝，即关注该用户的用户概况
         */
        $fans = $user->fans();
        $fanUsers = User::whereIn('id', $fans->pluck('fan_id'))
                ->withCount(['posts', 'fans', 'stars'])
                ->get();
        $title = $user->name . '的主页';
        return view('frontend.user.userdetail', compact('detailUser', 'posts', 'fanUsers', 'starUsers', 'title'));
    }

    // 关注某个用户
    public function doFocus(User $user) {
        $me = Auth::user();
        $me->doFocus($user->id);
        // 执行成功就返回一个json数组数据给前端的js
        return [
            'err' => 0,
            'msg' => ''
        ];
    }

    // 取消关注某个用户
    public function unFocus(User $user) {
        $me = Auth::user();
        $me->unFocus($user->id);
        // 执行成功就返回一个json数组数据给前端的js
        return [
            'err' => 0,
            'msg' => ''
        ];
    }

    // 个人空间
    public function userHome(User $user) {
        if ($user->id == Auth::id()) {
            $title = '我的空间';
            return view('frontend.user.home', compact('title'));
        } else {
            return redirect('/');
        }
    }

    // 个人设置页
    public function setting(User $user) {
        if ($user->id == Auth::id()) {
            $title = '设置用户信息';
            return view('frontend.user.setting', compact('user', 'title'));
        } else {
            return redirect('/');
        }
    }

    public function storeSetting(User $user) {
        if ($user->id == Auth::id()) {
            User::find(Auth::id());
            $title = '设置用户信息';
            return view('frontend.user.setting', compact('user', 'title'));
        } else {
            return redirect('/');
        }
    }

}
