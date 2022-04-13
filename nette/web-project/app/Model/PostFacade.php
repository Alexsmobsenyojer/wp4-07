<?php

namespace App\Model;

use Nette;

final class PostFacade
{
	use Nette\SmartObject;

	private Nette\Database\Explorer $database;

	public function __construct(Nette\Database\Explorer $database)
	{
		$this->database = $database;
	}

	public function getPublicArticles(int $limit, int $offset): Nette\Database\ResultSet
	{
		return $this->database->query('
			SELECT * FROM posts
			WHERE created_at < ?
			ORDER BY created_at DESC
			LIMIT ?
			OFFSET ?',
			new \DateTime, $limit, $offset
		);
		#return $this->database
		#	->table('posts')
		#	->where('created_at < ', new \DateTime)
		#	->order('created_at DESC');
	}

	public function getPostById(int $postId)
	{
		$post = $this->database
			->table('posts')
			->get($postId);
		return $post;
	}

	public function getComments(int $postId)
	{
		return $this->database
			->table('comments')
			->where('post_id', $postId);
	}

	public function editPost(int $postId, array $data)
	{
		$post = $this->database
			->table('posts')
			->get($postId);
		$post->update($data);
		return $post;
	}

	public function insertPost(array $data)
	{
		$post = $this->database
			->table('posts')
			->insert($data);
		return $post;
	}

	public function deletePost(int $postId)
	{
		$post = $this->database
			->table('posts')
			->get($postId);
		$post->delete();
	}

	public function addComment(int $postId, \stdClass $data)
	{
		$this->database->table('comments')->insert([
			'post_id' => $postId,
			'name' => $data->name,
			'email' => $data->email,
			'content' => $data->content,
		]);
	}

	public function addVisits(int $postId)
	{
		$views = $this->database->table('posts')->get($postId)->views_count;
		$views++;
		$data["views_count"] = $views;
		$this->database->table('posts')->get($postId)->update($data);
	}
	public function updateRating(int $postId, int $userId,int $like)
	{
		$currentrating = $this->database->table('rating')->get(['user_id' => $userId, 'post_id' => $postId]);
		if($currentrating != null)
		{
			$this->database
                ->query('UPDATE rating SET like_val = ? WHERE user_id = ? AND post_id = ?',
                 $like, $userId, $postId);
		}
		else
		{
			$this->database->table('rating')->insert(['user_id' => $userId, 'post_id' => $postId, 'like_val' => $like]);
		}
	}
	public function getUserRating(int $postId, int $userId)
	{
		$like = $this->database->table('rating')->where(['user_id' => $userId, 'post_id' => $postId]);
		if($like->count() == 0)
		{
			return null;
		} 
		else
		{
			return $like->fetch()->like_val;
		}
	}

	public function getPublishedArticlesCount(): int
	{
		return $this->database->fetchField('SELECT COUNT(*) FROM posts WHERE created_at < ?', new \DateTime);
	}
}
