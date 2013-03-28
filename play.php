<?php

use Arsenal\Database\Database;
use Arsenal\Database\DoctrineWrapper;
use Arsenal\Database\Schema;
use Arsenal\Database\Entity;
use Arsenal\Database\EntityQuery;
use Arsenal\Loggers\JsConsoleLogger;
use Arsenal\Loggers\HtmlLogger;
use Arsenal\Benchmark;

$db = new Database('mysql:host=localhost;dbname=arsenal', 'root', '');
$logger = new JsConsoleLogger;
$db->setLogger($logger);

$schema = new Schema;

$table = $schema->table('user');
    $table->column('id', 'serial');
    $table->column('username', 'string', 30);
    $table->column('email', 'string', 255, array('notNull'=>true));
    $table->column('password', 'string', 64);
$table->primary('id');
$table->unique('username');
$table->unique('email');

$table = $schema->table('post');
    $table->column('id', 'serial');
    $table->column('user_id', 'ref');
    $table->column('title', 'string', 255);
    $table->column('description', 'string', 1024);
    $table->column('body', 'text');
$table->primary('id');
$table->foreign('user_id', 'user', 'id', 'cascade', 'cascade');

$table = $schema->table('comment');
    $table->column('id', 'serial');
    $table->column('user_id', 'ref');
    $table->column('post_id', 'ref');
    $table->column('body', 'text');
$table->primary('id');
$table->foreign('user_id', 'user', 'id', 'cascade', 'cascade');
$table->foreign('post_id', 'post', 'id', 'cascade', 'cascade');

$docDb = new DoctrineWrapper($db);
$docDb->migrate($schema);

// $user = new Entity($db, 'user');
// $user->username = 'doris';
// // $user->email = 'silve.a@gmail.com';
// $user->password = sha1('123456');
// $user->save();

// $post = new Entity($db, 'post');
// $post->user_id = 8;
// $post->title = 'lorem ipsum';
// $post->description = 'lorem ipsum dolor sit amet';
// $post->body = 'lorem ipsum dolor sit amet. consecutamos avois dehar apendis escutos.';
// $post->save();

// $comment = new Entity($db, 'comment');
// $comment->user_id = 1;
// $comment->post_id = 2;
// $comment->body = 'lorem ipsum dolor sit comment...';
// $comment->save();

// $comment = new Entity($db, 'comment');
// $comment->user_id = 5;
// $comment->post_id = 9;
// $comment->body = 'lorem ipsum dolor sit comment...';
// $comment->save();

// $usersQuery = new EntityQuery($db, 'user');
// $postsQuery = new EntityQuery($db, 'post');
// $commentsQuery = new EntityQuery($db, 'comment');

// $users = $usersQuery->find();
// $posts = $postsQuery->find();
// $comments = $commentsQuery->find();

// $posts->assign('comments', $comments, 'id', 'post_id');
// $users->assign('posts', $posts, 'id', 'user_id');

// $posts->assignOne('author', $users, 'user_id', 'id');

// $query->where('id', '>=', 1);
// $results = $query->find();

// dump($users);

// $posts = $db->table('post')->with('user.username', 'author')->where('id', '<', 7)->find();
// if($posts->count())
// {
//     $users = $db->table('user')->where('id', 'in', $posts->user_id)->find();
//     $posts->assignOne('author', $users, 'user_id', 'id');
// }

// dump($posts);

// $user = $db->table('user')->where('id', '>=', 1)->findOne();
// if($user)
// {
//     $posts = $db->table('post')->where('user_id', $user->id)->find();
//     $user->posts = $posts;
// }

// dump($user);




$users = $db->entityQuery('user')->where('id', '>=', 1)->find();
$posts = $db->entityQuery('post')->where('user_id', 'in', $users->id)->find();
$comments = $db->entityQuery('comment')->where('post_id', 'in', $posts->id)->find();

$posts->assign('comments', $comments, 'id', 'post_id');
$users->assign('posts', $posts, 'id', 'user_id');

dump($users);



// $posts = $db->entityQuery('post')->where('id', '>=', 1)->find();
// $users = $db->entityQuery('user')->where('id', 'in', $posts->user_id)->find();

// $posts->assignOne('author', $users, 'user_id', 'id');

// dump($posts);