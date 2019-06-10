<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>FilmNight! : Testing</title>
    <link rel="stylesheet" href="resources/styles/testing.css"/>
</head>
<body>
    <div class="inner">
        <h1 class="title">Testing page</h1>
        <p class="info"><strong>Session:</strong> <?php var_dump(isset($_SESSION["user"]) ? $_SESSION["user"] : "You are not logged in."); ?></p>
        <p class="info"><strong>Info:</strong> Please use the browser back button to go back to this page.</p>
        <div class="group">
            <h2>Show results</h2>
            <p class="info"><strong>Info:</strong> The limit has been removed. Query will return ALL matching results.</p>
            <ul>
                <li><p><a href="/wai-assignment/server/?action=list&subject=films&limit=no">Show films</a></p></li>
                <li><p><a href="/wai-assignment/server/?action=list&subject=films&term=air&limit=no">Show search results ("air")</a></p></li>
                <li><p><a href="/wai-assignment/server/?action=list&subject=films&category=4&limit=no">Show category results (category_id: 4)</a></p></li>
                <li><p><a href="/wai-assignment/server/?action=list&subject=actors&film_id=3&limit=no">Show actors for an film (film_id: 3)</a></p></li>
                <li><p><a href="/wai-assignment/server/?action=list&subject=notes&limit=no">Show notes</a></p></li>
            </ul>
        </div>
        <div class="group">
            <h2>Login</h2>
            <form method="post" action="/wai-assignment/server/">
                <label for="email">Email address</label>
                <input type="email" id="email" name="email" required />
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required />
                <input type="hidden" name="action" value="login"/>
                <input type="hidden" name="subject" value="user"/><br/>
                <button type="submit">Login</button>
            </form>
        </div>
        <div class="group">
            <h2>Logout</h2>
            <form method="post" action="/wai-assignment/server/">
                <input type="hidden" name="action" value="logout"/>
                <input type="hidden" name="subject" value="user"/>
                <button type="submit">Logout</button>
            </form>
        </div>
        <div class="group">
            <h2>Add note</h2>
            <?php if (!isset($_SESSION["user"])) { ?>
                <p class="info"><strong>Info:</strong> Only logged in users can add notes.</p>
            <?php } ?>
            <form metod="post" action="/wai-assignment/server/">
                <input type="hidden" name="action" value="add"/>
                <input type="hidden" name="subject" value="note"/>
                <input type="hidden" name="email" value="<?php echo isset($_SESSION["user"]) ? $_SESSION["user"]["email"] : ""; ?>"/>
                <label for="film_id">Film ID</label>
                <input type="number" id="film_id" name="film_id"/>
                <label for="comment">Comment</label>
                <textarea id="comment" name="comment"></textarea><br/>
                <button type="submit">Add note</button>
            </form>
        </div>
        <div class="group">
            <h2>Edit note</h2>
            <?php if (!isset($_SESSION["user"])) { ?>
                <p class="info"><strong>Info:</strong> Only logged in users can edit notes.</p>
            <?php } ?>
            <?php 
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, "http://localhost/wai-assignment/server/?action=list&subject=notes&film_id=1");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $output = curl_exec($ch);
                curl_close($ch);
                $output = json_decode($output, 1);
            ?>
            <p class="info"><strong>Info:</strong> 
            <?php echo
                isset($_SESSION["user"]) && ($_SESSION["user"]["email"] == $output["notes"]["result"][0]["user"]) 
                ? "You are logged in as the note author"
                : "You are not the note author";
            ?>.
            </p>
            <form method="post" action="/wai-assignment/server/">
                <input type="hidden" name="action" value="update"/>
                <input type="hidden" name="subject" value="note"/>
                <input type="hidden" name="user" value="<?php echo isset($_SESSION["user"]) ? $_SESSION["user"]["email"] : "";?>"/>
                <label for="film_id">Film ID</label>
                <input type="number" id="film_id" name="film_id" value="<?php echo $output["notes"]["result"][0]["film_id"]; ?>" />
                <label for="author">Author</label>
                <input type="text" id="author" value="<?php echo $output["notes"]["result"][0]["user"]; ?>" />
                <label for="edit_comment">Comment</label>
                <textarea id="edit_comment" name="comment"><?php echo $output["notes"]["result"][0]["comment"]; ?></textarea><br/>
                <button type="submit">Save note</button>
            </form>
        </div>
    </div>
</body>
</html>