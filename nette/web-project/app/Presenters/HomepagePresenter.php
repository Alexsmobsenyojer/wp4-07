<?php

declare(strict_types=1);

namespace App\Presenters;
use App\Model\PostFacade;
#use App\Model\UserFacade;
use Nette;
use Nette\Application\UI\Form;

final class HomepagePresenter extends Nette\Application\UI\Presenter
{ 
    private PostFacade $facade;
	#private UserFacade $userFacade;
	public function __construct(PostFacade $facade, /*UserFacade $userFacade*/)
	{
		$this->facade = $facade;
		#$this->userFacade = $userFacade;
	}

	public function renderDefault(int $page = 1): void
	{
		$articlesCount = $this->facade->getPublishedArticlesCount();
		$paginator = new Nette\Utils\Paginator;
		$paginator->setItemCount($articlesCount);
		$paginator->setItemsPerPage(3);
		$paginator->setPage($page);
		#$this->userFacade->add("Admin", "admin@ossp.cz", "secret");
		#$this->template->posts = $this->facade
		#	->getPublicArticles()
		#	->limit(8);
		$posts = $this->facade->getPublicArticles($paginator->getLength(), $paginator->getOffset());
	    $this->template->posts = $posts;
		$this->template->paginator = $paginator;	
		$this->redrawControl("paginator");
	}
	public function handleShowRndNmr()
	{ $this->template->number = rand(1, 100); $this->redrawControl("randomNumber"); }
}
