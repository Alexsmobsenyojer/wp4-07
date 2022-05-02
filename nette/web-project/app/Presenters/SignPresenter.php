<?php
namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;
use App\Model\UserFacade;

final class SignPresenter extends Nette\Application\UI\Presenter
{
	private UserFacade $userfacade;
	public function __construct(UserFacade $userfacade)
	{
		$this->userfacade = $userfacade;
	}
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
	protected function createComponentSignUpForm(): Form
	{
		$form = new Form;
		$form->addText('username', 'Kultistická přezdívka:')
			->setRequired('Vytvořte si své uživatelské jméno.');
		$form->addEmail('email', 'E-mail:')
			->setRequired('Zadejte váš e-mail.');
		$form->addPassword('password', 'Heslo:')
			->setRequired('Vytvořte si své heslo.')
			->addRule(Form::MIN_LENGTH, 'Minimální délka hesla je %d znaků', 6)
		;

		$form->addSubmit('send', 'Potvrdit krví');
		$form->onSuccess[] = [$this, 'signUpFormSucceeded'];
		return $form;
	}
	public function signUpFormSucceeded(Form $form, \stdClass $data): void
	{
	if ($this->getUser()->isLoggedIn()) { $userId = $this->getUser()->getId();
			$this->userfacade->change($data->username, $data->email, $data->password, $userId);
			$this->flashMessage('Uživatel byl úspěšně upraven.', 'success');
		} 
	else {	
		try {
			$this->userfacade->add($data->username, $data->email, $data->password);
			$this->flashMessage('Úspěšně jste se zaregistrovali.');
			$this->redirect('Sign:in');			
		} catch (Nette\Security\AuthenticationException $e) {
			$form->addError('IMPOSTER.');
		}
	    }
	}
	public function actionOut(): void
	{
		$this->getUser()->logout();
		$this->flashMessage('Miss you so much');
		$this->redirect('Homepage:');
	}
}
