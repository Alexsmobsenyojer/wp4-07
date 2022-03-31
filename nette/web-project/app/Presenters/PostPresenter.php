<?php

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;
use App\Model\PostFacade;

final class PostPresenter extends Nette\Application\UI\Presenter
{
	private PostFacade $facade;

	public function __construct(PostFacade $facade)
	{
		$this->facade = $facade;
	}
	public function actionShow(int $postId): void
	{
		$post = $this->facade->getPostById($postId);
		$this->getUser()->isLoggedIn();
		if ($post->status == "ARCHIVED" && !$this->getUser()->isLoggedIn()) {
			$this->flashMessage("Nemáte potřebné oprávnění");
			$this->redirect('Sign:in');
		}
	}
	public function renderShow(int $postId): void
	{
		$post = $this->facade->getPostById($postId);
		if (!$post) {$this->error('Stránka nebyla nalezena');}
		$this->facade->addVisits($postId);
		$this->template->post = $post;
		$this->template->comments = $this->facade->getComments($postId);
		$this->template->like = $this->facade->getUserRating($postId, $this->getUser()->getId());
	}

	protected function createComponentCommentForm(): Form
	{
		$form = new Form;

		$form->addText('name', 'Jméno:')
			->setRequired();

		$form->addEmail('email', 'E-mail:');

		$form->addTextArea('content', 'Komentář:')
			->setRequired();

		$form->addSubmit('send', 'Publikovat komentář');
		$form->onSuccess[] = [$this, 'commentFormSucceeded'];
		return $form;
	}

	public function commentFormSucceeded(\stdClass $data): void
	{
		$postId = $this->getParameter('postId');

		$this->facade->addComment($postId, $data);

		$this->flashMessage('Děkuji za komentář', 'success');
		$this->redirect('this');
	}
	public function handleLike(int $like, int $userId, int $postId)
	{
		if ($this->getUser()->isLoggedIn()) {
			$userId = $this->getUser()->getId();
			$this->facade->updateRating($postId, $userId, $like);
			$this->redirect('this');
		} else {
			$this->flashMessage('Pro přidání hodnocení se musíte přihlásit', 'failed');
			$this->redirect('Sign:in');
		}
	}
}
