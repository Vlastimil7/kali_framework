<?php

namespace Controllers\Front;

use Core\Controller;
use Core\Request;
use Helpers\Flash;
use Helpers\Toast;
use Helpers\Validator;

final class HomeController extends Controller
{
    public function index(): void
    {
        $this->view('home/index', [
            'title' => __('home_title') . ' | ' . config('app.name'),
            'description' => __('home_description'),
            'demoErrors' => Validator::flashedErrors('demo'),
            'demoOld' => Flash::old('demo'),
            'demoNotice' => Flash::get('demo_notice', ''),
        ]);
    }

    public function validateDemo(Request $request): void
    {
        $input = [
            'name' => is_string($request->post('name')) ? trim($request->post('name')) : '',
            'email' => is_string($request->post('email')) ? trim($request->post('email')) : '',
        ];

        $validator = Validator::make($input, [
            'name' => 'required|string|min:2|max:60',
            'email' => 'required|email|max:160',
        ], [
            'name.required' => __('demo_name_required'),
            'name.min' => __('demo_name_min'),
            'name.max' => __('demo_name_max'),
            'email.required' => __('demo_email_required'),
            'email.email' => __('demo_email_invalid'),
            'email.max' => __('demo_email_max'),
        ]);

        if ($validator->fails()) {
            $validator->flash('demo', $input, __('demo_error_title'));
        } else {
            $message = __('demo_form_success_message', ['name' => $input['name']]);
            Flash::set('demo_notice', $message);
            Toast::success($message, __('demo_success_title'));
        }

        header('Location: ' . locale_url() . '#demo', true, 303);
    }

    public function hello(string $name): void
    {
        $this->view('home/index', [
            'title' => __('welcome', ['name' => $name]) . ' | ' . config('app.name'),
            'description' => __('home_description'),
            'name' => $name,
        ]);
    }
}
