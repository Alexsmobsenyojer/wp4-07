<?php
namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;
use App\Model\PostFacade;
use Nette\Utils\Random;
final class EditPresenter extends Nette\Application\UI\Presenter
{
	private PostFacade $facade;

public function __construct(PostFacade $facade)
	{
		$this->facade = $facade;
	}

public function startup(): void
	{
		parent::startup();
	
		if (!$this->getUser()->isLoggedIn()) {
			$this->redirect('Sign:in');
		}
	}

protected function createComponentPostForm(): Form
    {
	$form = new Form;
	$form->addText('title', 'Titulek:')
		->setRequired();
	$form->addTextArea('content', 'Obsah:')
		->setRequired();
	$form->addUpload('image', 'Náhledový obrázek:')
		->addRule(Form::IMAGE, 'Thumbnail must be JPEG, PNG or GIF');
	$form->addUpload('images', 'Další obrázky:', true)
		->addRule(Form::IMAGE, 'Thumbnail must be JPEG, PNG or GIF');
	$statuses = [
            'OPEN' => 'OTEVŘENÝ',
            'CLOSED' => 'UZAVŘENÝ',
            'ARCHIVED' => 'ARCHIVOVANÝ'
        ];
    $form->addSelect('status', 'Stav:', $statuses)
         ->setDefaultValue('OPEN');
	$categories= $this->facade->getCategories()->fetchPairs('id', 'name');
    $form->addSelect('category_id', 'Kategorie:', $categories);
	$form->addSubmit('send', 'Uložit a publikovat');
	$form->onSuccess[] = [$this, 'postFormSucceeded'];

	return $form;
    }
 
public function postFormSucceeded($form, $data): void
    {
        $postId = $this->getParameter('postId');
		if ($data->image->hasFile()) { //kdyz je soubor skutecne poslan z formu
			if ($data->image->isOk()) { //prejmenovani souboru
			$data->image->move('upload/'. $data->image->getSanitizedName());
			$data['image'] = ('upload/'. $data->image->getSanitizedName());
			}
			} else {
				unset($data->image);
			$this->flashMessage('Obrázek nebyl přidán ', 'failed');
			//$this->redirect('this');
			}
			foreach ($data->images as $photo) {
				$photo->move('upload/'. $photo->getSanitizedName());
				$data['images'][] = ('upload/'. $photo->getSanitizedName());
				if ($postId) {$this->facade->insertImage($postId, $photo);}
			}
			unset($data->images);
		if ($postId) {$post=$this->facade->editPost($postId,(array) $data);} 
		else {$post=$this->facade->insertPost((array)$data);}
        
		$this->flashMessage("Příspěvek byl úspěšně publikován.", 'success');
	    $this->redirect('Post:show', $post->id);
	
    }
  public function renderEdit(int $postId): void
  {
	$post = $this->facade->getPostById($postId);

	if (!$post) {
		$this->error('Post not found');
	}

	$this->getComponent('postForm')
		->setDefaults($post->toArray());
		$this->template->post = $post;
  }
 
  

  public function handleDelete(int $postId)
  {$data["image"]= null;
	  $this->facade->editPost($postId, $data);
	  $this->flashMessage("Obrázek byl smazán");
	  $this->redrawControl("image");
   #$this->redirect("Post:show",$postId);
  }
  public function handleDeletePost(int $postId)
  {   $this->facade->deletePost($postId);
	  $this->flashMessage("Příspěvek byl smazán");
	  $this->redirect("Homepage:default");
  }

}