<?php
require_once __DIR__ . '/../controllers/PostController.php';

if ($request === '/create-post' && $method === 'POST') {
    createPost($pdo);
}

elseif (preg_match('#^/update-post/(\d+)$#', $request, $matches) && $method === 'PUT') {
    $postId = $matches[1];
    updatePost($pdo, $postId);
}

elseif (preg_match('#^/delete-post/(\d+)$#', $request, $matches) && $method === 'DELETE') {
    $postId = $matches[1];
    deletePost($pdo, $postId);
}

elseif ($request === '/posts' && $method === 'GET') {
    fetchPosts($pdo);
}

elseif (preg_match('#^/post/(\d+)$#', $request, $matches) && $method === 'GET') {
    $postId = $matches[1];
    getPost($pdo, $postId);
}

elseif (preg_match('#^/comment-post/(\d+)$#', $request, $matches) && $method === 'POST') {
    $postId = $matches[1];
    commentPost($pdo, $postId);
}

elseif (preg_match('#^/like-post/(\d+)$#', $request, $matches) && $method === 'POST') {
    $postId = $matches[1];
    likePost($pdo, $postId);
}

elseif (preg_match('#^/unlike-post/(\d+)$#', $request, $matches) && $method === 'POST') {
    $postId = $matches[1];
    unlikePost($pdo, $postId);
}

elseif ($request === '/user-posts' && $method === 'GET') {
    fetchUserPosts($pdo);
}
