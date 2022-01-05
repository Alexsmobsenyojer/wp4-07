<?php
namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;

final class PostPresenter extends Nette\Application\UI\Presenter
{
	private Nette\Database\Explorer $database;

	public function __construct(Nette\Database\Explorer $database)
	{
		$this->database = $database;
	}

	public function renderShow(int $postId): void
{
	$post = $this->database
		->table('posts')
		->get($postId);
	if (!$post) {
		$this->error('Stránka nebyla nalezena');
	}

	$this->template->post = $post;
}
}