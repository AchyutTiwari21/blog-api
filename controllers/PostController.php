<?php

function createPost($pdo) {
    // Auth from cookies
    $email = $_COOKIE['email'] ?? null;
    $token = $_COOKIE['token'] ?? null;

    if (!$email || !$token) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }

    // Validate token
    $stmt = $pdo->prepare("SELECT Id FROM Users WHERE Email = ? AND Token = ?");
    $stmt->execute([$email, $token]);
    $user = $stmt->fetch();

    if (!$user) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid credentials']);
        exit;
    }

    // Get post data
    $data = json_decode(file_get_contents("php://input"), true);
    $title = $data['title'] ?? null;
    $description = $data['description'] ?? null;
    $featuredImage = $data['featuredImage'] ?? null;

    if (!$title || !$description) {
        http_response_code(400);
        echo json_encode(['error' => 'Title and description are required']);
        exit;
    }

    // Insert post
    $stmt = $pdo->prepare("INSERT INTO Posts (Title, Description, FeaturedImage, UserId) VALUES (?, ?, ?, ?)");
    $success = $stmt->execute([$title, $description, $featuredImage, $user['Id']]);

    if ($success) {
        $postId = $pdo->lastInsertId();
        $stmt2 = $pdo->prepare("SELECT * FROM Posts WHERE Id = ?");
        $stmt2->execute([$postId]);
        $post = $stmt2->fetch(PDO::FETCH_ASSOC);
    
        http_response_code(201);
        echo json_encode([
            'message' => 'Post created successfully',
            'post' => $post
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to create post']);
    }
}

function updatePost($pdo, $postId) {
    $email = $_COOKIE['email'] ?? null;
    $token = $_COOKIE['token'] ?? null;

    if (!$email || !$token) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }

    // Get user
    $stmt = $pdo->prepare("SELECT Id FROM Users WHERE Email = ? AND Token = ?");
    $stmt->execute([$email, $token]);
    $user = $stmt->fetch();

    if (!$user) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid user']);
        exit;
    }

    // Check if user owns the post
    $stmt = $pdo->prepare("SELECT * FROM Posts WHERE Id = ? AND UserId = ?");
    $stmt->execute([$postId, $user['Id']]);
    $post = $stmt->fetch();

    if (!$post) {
        http_response_code(403);
        echo json_encode(['error' => 'Post not found or access denied']);
        exit;
    }

    $data = json_decode(file_get_contents("php://input"), true);
    $title = $data['title'] ?? $post['Title'];
    $description = $data['description'] ?? $post['Description'];
    $featuredImage = $data['featuredImage'] ?? $post['FeaturedImage'];

    $stmt = $pdo->prepare("UPDATE Posts SET Title = ?, Description = ?, FeaturedImage = ? WHERE Id = ?");
    $stmt->execute([$title, $description, $featuredImage, $postId]);

    http_response_code(200);
    echo json_encode(['message' => 'Post updated successfully']);
}

function deletePost($pdo, $postId) {
    $email = $_COOKIE['email'] ?? null;
    $token = $_COOKIE['token'] ?? null;

    if (!$email || !$token) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }

    // Get user
    $stmt = $pdo->prepare("SELECT Id FROM Users WHERE Email = ? AND Token = ?");
    $stmt->execute([$email, $token]);
    $user = $stmt->fetch();

    if (!$user) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid user']);
        exit;
    }

    // Check if post belongs to user
    $stmt = $pdo->prepare("SELECT * FROM Posts WHERE Id = ? AND UserId = ?");
    $stmt->execute([$postId, $user['Id']]);
    $post = $stmt->fetch();

    if (!$post) {
        http_response_code(403);
        echo json_encode(['error' => 'Post not found or access denied']);
        exit;
    }

    $stmt = $pdo->prepare("DELETE FROM Posts WHERE Id = ?");
    $stmt->execute([$postId]);

    http_response_code(200);
    echo json_encode(['message' => 'Post deleted successfully']);
}

function fetchPosts($pdo) {
    // Step 1: Fetch posts with author info
    $stmt = $pdo->prepare("
        SELECT 
            Posts.Id, 
            Posts.Title, 
            Posts.Description, 
            Posts.FeaturedImage, 
            Posts.UserId,
            Posts.Likes,
            Users.Name as AuthorName,
            Users.ProfileImage as AuthorProfileImage,
            Users.Designation as AuthorDesignation
        FROM Posts
        JOIN Users ON Posts.UserId = Users.Id
        ORDER BY Posts.Id DESC
    ");
    $stmt->execute();
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Step 2: Get all post IDs
    $postIds = array_column($posts, 'Id');

    if (!empty($postIds)) {
        // Prepare placeholders for IN clause
        $placeholders = implode(',', array_fill(0, count($postIds), '?'));

        // Step 3: Fetch all comments for these posts with commenter info
        $stmt = $pdo->prepare("
            SELECT 
                Comments.PostId,
                Comments.Comment,
                Users.Name as CommenterName,
                Users.ProfileImage as CommenterProfileImage
            FROM Comments
            JOIN Users ON Comments.UserId = Users.Id
            WHERE Comments.PostId IN ($placeholders)
        ");
        $stmt->execute($postIds);
        $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Step 4: Group comments by PostId
        $commentsByPost = [];
        foreach ($comments as $comment) {
            $commentsByPost[$comment['PostId']][] = [
                'Comment' => $comment['Comment'],
                'CommenterName' => $comment['CommenterName'],
                'CommenterProfileImage' => $comment['CommenterProfileImage']
            ];
        }

        // Step 5: Add comments to corresponding post
        foreach ($posts as &$post) {
            $post['Comments'] = $commentsByPost[$post['Id']] ?? [];
        }
    }

    http_response_code(200);
    echo json_encode(['posts' => $posts]);
    
}

function commentPost($pdo, $postId) {

    $email = $_COOKIE['email'] ?? null;
    $token = $_COOKIE['token'] ?? null;

    if (!$email || !$token) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT Id FROM Users WHERE Email = ? AND Token = ?");
    $stmt->execute([$email, $token]);
    $user = $stmt->fetch();

    if (!$user) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid user']);
        exit;
    }

    $data = json_decode(file_get_contents("php://input"), true);
    $comment = trim($data['comment'] ?? '');

    if (empty($comment)) {
        http_response_code(400);
        echo json_encode(['error' => 'Comment is required']);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO Comments (Comment, PostId, UserId) VALUES (?, ?, ?)");
    $stmt->execute([$comment, $postId, $user['Id']]);

    http_response_code(201);
    echo json_encode(['message' => 'Comment added successfully']);
}

function likePost($pdo, $postId) {

    $email = $_COOKIE['email'] ?? null;
    $token = $_COOKIE['token'] ?? null;

    if (!$email || !$token) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }

    // Authenticate the user
    $stmt = $pdo->prepare("SELECT Id FROM Users WHERE Email = ? AND Token = ?");
    $stmt->execute([$email, $token]);
    $user = $stmt->fetch();

    if (!$user) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid user']);
        exit;
    }

    $userId = $user['Id'];

    // Check if user already liked the post
    $stmt = $pdo->prepare("SELECT Id FROM PostLikes WHERE UserId = ? AND PostId = ?");
    $stmt->execute([$userId, $postId]);
    $likeExists = $stmt->fetch();

    if ($likeExists) {
        http_response_code(400);
        echo json_encode(['error' => 'You have already liked this post']);
        exit;
    }

    // Like the post
    $pdo->beginTransaction();

    // Insert into PostLikes
    $stmt = $pdo->prepare("INSERT INTO PostLikes (UserId, PostId) VALUES (?, ?)");
    $stmt->execute([$userId, $postId]);

    // Increment like count in Posts table
    $stmt = $pdo->prepare("UPDATE Posts SET Likes = Likes + 1 WHERE Id = ?");
    $stmt->execute([$postId]);

    $pdo->commit();

    http_response_code(200);
    echo json_encode(['message' => 'Post liked successfully']);
}

function unlikePost($pdo, $postId) {
    $email = $_COOKIE['email'] ?? null;
    $token = $_COOKIE['token'] ?? null;

    if (!$email || !$token) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }

    // Fetch the user
    $stmt = $pdo->prepare("SELECT Id FROM Users WHERE Email = ? AND Token = ?");
    $stmt->execute([$email, $token]);
    $user = $stmt->fetch();

    if (!$user) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid user']);
        exit;
    }

    // Check if user already liked the post
    $stmt = $pdo->prepare("SELECT Id FROM PostLikes WHERE UserId = ? AND PostId = ?");
    $stmt->execute([$user['Id'], $postId]);
    $like = $stmt->fetch();

    if (!$like) {
        http_response_code(400);
        echo json_encode(['error' => 'You haven’t liked this post yet']);
        exit;
    }

    // Remove like and decrease count
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("DELETE FROM PostLikes WHERE UserId = ? AND PostId = ?");
    $stmt->execute([$user['Id'], $postId]);

    $stmt = $pdo->prepare("UPDATE Posts SET Likes = Likes - 1 WHERE Id = ? AND Likes > 0");
    $stmt->execute([$postId]);

    $pdo->commit();

    http_response_code(200);
    echo json_encode(['message' => 'Post unliked successfully']);
}
