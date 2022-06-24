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
			->addRule(Form::MIN_LENGTH, 'Minimální délka hesla je %d znaků', 6);

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
	protected function createComponentEditNameForm(): Form
	{
		$form = new Form;
		$form->addText('username', 'Nová Přezdívka:')
			->setRequired('Vytvořte si své uživatelské jméno.');
		$form->addSubmit('send', 'Změnit jméno');
		$form->onSuccess[] = [$this, 'editNameFormSucceeded'];
		return $form;
	}
	public function editNameFormSucceeded(Form $form, \stdClass $data): void
	{
	$userId = $this->getUser()->getId();
	$this->userfacade->changename($data->username, $userId);
	$this->flashMessage('Přezdívka byla úspěšně změněna.', 'success');
	$this->getUser()->logout();
	$this->redirect('Sign:in');
	}
	protected function createComponentEditEmailForm(): Form
	{
		$form = new Form;
		$form->addEmail('email', 'Nový E-mail:')
			->setRequired('Zadejte váš e-mail.');
		$form->addSubmit('send', 'Změnit e-mail');
		$form->onSuccess[] = [$this, 'editEMailFormSucceeded'];
		return $form; 
	}
	public function editEMailFormSucceeded(Form $form, \stdClass $data): void
	{
	$userId = $this->getUser()->getId();
	$this->userfacade->changeemail($data->email, $userId);
	$this->flashMessage('Email byl úspěšně změněn.', 'success');
	$this->getUser()->logout();
	$this->redirect('Sign:in');
	}
	protected function createComponentEditPassForm(): Form
	{
		$form = new Form;
		$form->addPassword('password', 'Nové Heslo:')
			->setRequired('Vytvořte si své heslo.')
			->addRule(Form::MIN_LENGTH, 'Minimální délka hesla je %d znaků', 6);
		$form->addSubmit('send', 'Změnit heslo');
		$form->onSuccess[] = [$this, 'editPassFormSucceeded'];
		return $form;
	}
	public function editPassFormSucceeded(Form $form, \stdClass $data): void
	{
	$userId = $this->getUser()->getId();
	$this->userfacade->changename($data->password, $userId);
	$this->flashMessage('Heslo bylo úspěšně změněno.', 'success');
	$this->getUser()->logout();
	$this->redirect('Sign:in');
	}
	protected function createComponentPfpForm(): Form
	{
	$form = new Form;
	$form->addUpload('image', 'Nový Profilový obrázek:')
	    ->setRequired()
		->addRule(Form::IMAGE, 'Profile picture must be JPEG, PNG or GIF');
		$form->addSubmit('send', 'Změnit Profilový obrázek');
		$form->onSuccess[] = [$this, 'PfpFormSucceeded'];
		return $form;
	}
	public function pfpFormSucceeded(Form $form, \stdClass $data): void
	{
	$userId = $this->getUser()->getId();
	$data->image->move('upload/'. $data->image->getSanitizedName());
	$data->image = ('upload/'. $data->image->getSanitizedName());
	$this->userfacade->changepfp($data->image, $userId);
	$this->flashMessage('Profilový obrázek byl změněn.', 'success');
	$this->getUser()->logout();
	$this->redirect('Sign:in');
	}
	public function actionOut(): void
	{
		$this->getUser()->logout();
		$this->flashMessage('Byl jste odhlášen Miss you so much');
		$this->redirect('Homepage:');
	}
}
