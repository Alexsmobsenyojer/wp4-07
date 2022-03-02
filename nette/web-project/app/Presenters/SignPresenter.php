<?php
namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;


final class SignPresenter extends Nette\Application\UI\Presenter
{
protected function createComponentSignInForm(): Form
	{
		$form = new Form;
		$form->addText('username', 'Kultistická přezdívka:')
			->setRequired('Prosím vyplňte své uživatelské jméno.');

		$form->addPassword('password', 'Heslo:')
			->setRequired('Prosím vyplňte své heslo.');

		$form->addSubmit('send', 'Potvrdit krví');

		$form->onSuccess[] = [$this, 'signInFormSucceeded'];
		return $form;
	}
public function signInFormSucceeded(Form $form, \stdClass $data): void
    {
	try {
		$this->getUser()->login($data->username, $data->password);
		$this->redirect('Homepage:');

	    } catch (Nette\Security\AuthenticationException $e) {
		$form->addError('IMPOSTER.');
	     }
    }
public function actionOut(): void
    {
	$this->getUser()->logout();
	$this->flashMessage('Miss you so much');
	$this->redirect('Homepage:');
    }
}