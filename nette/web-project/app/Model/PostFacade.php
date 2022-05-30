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
		return $this->database->query(
			'
			SELECT p.id, p.title, p.content, p.views_count, p.created_at, p.status, p.image,c.id AS category_id, c.name AS category_name  FROM posts p
			LEFT JOIN categories c ON p.category_id = c.id
			WHERE created_at < ?
			ORDER BY created_at DESC
			LIMIT ?
			OFFSET ?',
			new \DateTime,
			$limit,
			$offset
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

	public function getPostsByCategory(int $categoryId)
	{
		return $this->database
			->table('posts')
			->where(['category_id' => $categoryId]);
	}

	public function getComments(int $postId)
	{
		#return $this->database
			#->table('comments')
			#->where('post_id', $postId);
			return $this->database->query(
				'
				SELECT c.id, c.content, c.email, c.name, u.id AS user_id, u.pfp AS user_pfp  FROM comments c
				LEFT JOIN users u ON c.user_id = u.id
				WHERE post_id  = ?',$postId
			);
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
		$likes = $this->database->table('rating')->where(['post_id' => $postId]);
		$likes->delete();
		$comments = $this->database->table('comments')->where(['post_id' => $postId]);
		$comments->delete();
		$post = $this->database->table('posts')->get($postId);
		$post->delete();
	}

	public function addComment(int $postId, \stdClass $data, $logged, $userId)
	{
		if ($logged == true) {
			$user = $this->database->table('users')->get($userId);
			$this->database->table('comments')->insert([
				'post_id' => $postId,
				'name' => $user->username,
				'email' => $user->email,
				'content' => $data->content,
				'user_id' => $userId
			]);
		} else {
			$this->database->table('comments')->insert([
				'post_id' => $postId,
				'name' => $data->name,
				'email' => $data->email,
				'content' => $data->content,
			]);
		}
	}
	public function getComment(int $commentId)
	{
		return $this->database->table('comments')->where(['id' => $commentId]);
	}

	public function addVisits(int $postId)
	{
		$views = $this->database->table('posts')->get($postId)->views_count;
		$views++;
		$data["views_count"] = $views;
		$this->database->table('posts')->get($postId)->update($data);
	}
	public function updateRating(int $postId, int $userId, int $like)
	{
		$currentrating = $this->database->table('rating')->get(['user_id' => $userId, 'post_id' => $postId]);
		if ($currentrating != null) {
			$this->database
				->query(
					'UPDATE rating SET like_val = ? WHERE user_id = ? AND post_id = ?',
					$like,
					$userId,
					$postId
				);
		} else {
			$this->database->table('rating')->insert(['user_id' => $userId, 'post_id' => $postId, 'like_val' => $like]);
		}
	}
	public function getUserRating(int $postId, int $userId)
	{
		$like = $this->database->table('rating')->where(['user_id' => $userId, 'post_id' => $postId]);
		if ($like->count() == 0) {
			return null;
		} else {
			return $like->fetch()->like_val;
		}
	}

	public function getPublishedArticlesCount(): int
	{
		return $this->database->fetchField('SELECT COUNT(*) FROM posts WHERE created_at < ?', new \DateTime);
	}
	public function getCategories()
	{
		return $this->database->table('categories');
	}
	public function getCategory($categoryId)
	{
		return $this->database->table('categories')->get($categoryId);
	}
	public function getCategoryname($categoryId)
	{
		return $this->database->table('categories')->where(["id" => $categoryId])->fetch()->name;
	}
    public function countLikes(int $postId)
	{
		return $this->database->fetchField('SELECT COUNT(*) FROM rating WHERE post_id = ? AND like_val = 1', $postId);
	}
	public function countDisLikes(int $postId)
	{
		return $this->database->fetchField('SELECT COUNT(*) FROM rating WHERE post_id = ? AND like_val = 2', $postId);
	}
	public function getPostsBySearch($search)
	{
		return $this->database->query(
			'SELECT * FROM posts WHERE title LIKE ? OR content LIKE ?',
			'%' . $search . '%',
			'%' . $search . '%'
		);
	}

}
