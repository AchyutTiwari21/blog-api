<?php
require_once __DIR__ . '/../controllers/PostController.php';

if ($request === '/blog-api/create-post' && $method === 'POST') {
    createPost($pdo);
}

elseif (preg_match('#^/blog-api/update-post/(\d+)$#', $request, $matches) && $method === 'PUT') {
    $postId = $matches[1];
    updatePost($pdo, $postId);
}

elseif (preg_match('#^/blog-api/delete-post/(\d+)$#', $request, $matches) && $method === 'DELETE') {
    $postId = $matches[1];
    deletePost($pdo, $postId);
}

elseif ($request === '/blog-api/posts' && $method === 'GET') {
    fetchPosts($pdo);
}

elseif (preg_match('#^/blog-api/post/(\d+)$#', $request, $matches) && $method === 'GET') {
    $postId = $matches[1];
    getPost($pdo, $postId);
}

elseif (preg_match('#^/blog-api/comment-post/(\d+)$#', $request, $matches) && $method === 'POST') {
    $postId = $matches[1];
    commentPost($pdo, $postId);
}

elseif (preg_match('#^/blog-api/like-post/(\d+)$#', $request, $matches) && $method === 'POST') {
    $postId = $matches[1];
    likePost($pdo, $postId);
}

elseif (preg_match('#^/blog-api/unlike-post/(\d+)$#', $request, $matches) && $method === 'POST') {
    $postId = $matches[1];
    unlikePost($pdo, $postId);
}

elseif ($request === '/blog-api/user-posts' && $method === 'GET') {
    fetchUserPosts($pdo);
}
