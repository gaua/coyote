<?php

namespace Coyote\Http\Controllers\Auth;

use Coyote\Actkey;
use Coyote\Http\Factories\MailFactory;
use Coyote\Repositories\Contracts\UserRepositoryInterface as UserRepository;
use Coyote\Http\Controllers\Controller;
use Coyote\Services\Stream\Activities\Confirm as Stream_Confirm;
use Coyote\Services\Stream\Actor as Stream_Actor;
use Coyote\Services\Stream\Objects\Person as Stream_Person;
use Illuminate\Http\Request;
use Illuminate\Mail\Message;

class ConfirmController extends Controller
{
    use MailFactory;

    /**
     * @var UserRepository
     */
    private $user;

    /**
     * ConfirmController constructor.
     * @param UserRepository $user
     */
    public function __construct(UserRepository $user)
    {
        parent::__construct();

        $this->user = $user;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        if ($this->userId && $request->user()->is_confirm) {
            return redirect()->route('user.home')->with('success', 'Adres e-mail jest już potwierdzony.');
        }
        $this->breadcrumb->push('Potwierdź adres e-mail', url('Confirm'));

        return $this->view('auth.confirm');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function generateLink(Request $request)
    {
        $this->validate($request, [
            // case sensitive
            'email' => 'required|email|max:255|exists:users|unique:users,email,NULL,id,is_confirm,1',
            'name'  => 'sometimes|username|exists:users'
        ], [
            'email.unique' => 'Ten adres e-mail jest już zweryfikowany.'
        ]);

        if ($this->userId) {
            // perhaps user decided to change his email, so we need to save new one in database
            if ($request->email !== $request->user()->email) {
                $request->user()->fill(['email' => $request->email])->save();
            }

            $userId = $this->userId;
        } else {
            $result = $this->user->findWhere($request->intersect(['name', 'email']) + ['is_confirm' => 0]);

            // taka sytuacja nie bedzie miala miejsce w 99% przypadkow
            // warunek zostanie spelniony tylko wowczas gdy np. 2 lub wiecej uzytkownikow zostalo
            // zarejestrowanych na ten sam adres e-mail
            if ($result->count() > 1) {
                return back()
                    ->withInput()
                    ->withErrors('email', 'Ten e-mail przypisany jest do dwóch kont. Wybierz, które z nich ma zostać potwierdzone')
                    ->with('users', $result->pluck('name', 'name')->toArray());
            }

            $userId = $result->first()->id;
        }

        $url = Actkey::createLink($userId);
        $email = $request->email;

        $this->getMailFactory()->queue('emails.user.change_email', ['url' => $url], function (Message $message) use ($email) {
            $message->to($email);
            $message->subject('Prosimy o potwierdzenie nowego adresu e-mail');
        });

        return back()->with('success', 'Na podany adres e-mail został wysłany link aktywacyjny.');
    }

    /**
     * Potwierdzenie adresu e-mail poprzez link aktywacyjny znajdujacy sie w mailu
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function email(Request $request)
    {
        $actkey = Actkey::where('user_id', $request->input('id'))->where('actkey', $request->input('actkey'))->firstOrFail();

        $user = $this->user->findOrFail($request->get('id'));
        $user->is_confirm = 1;

        if ($actkey->email) {
            $user->email = $actkey->email;
        }

        $user->save();
        $user->actkey()->delete();

        // potwierdzajac adres email, uzytkownik moze nie byc zalogowany. przekazujemy wiec model User
        // jako aktora aby zapisal sie w bazie danych.
        stream(new Stream_Confirm(new Stream_Actor($user), new Stream_Person($user->toArray())));

        return redirect()->route('home')->with('success', 'Adres e-mail został pozytywnie potwierdzony.');
    }
}
