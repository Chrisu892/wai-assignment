<?php

/**
 * This source code is an adaptation of the following code snippets:
 * 
 * Title: Single Point of Entry
 * Author: Rob Davis
 * Date: no date
 * Code version: no version
 * Availability: https://wheel.x-alt.com/book/1/chapter/7/page/3
 * 
 * Title: Edit a record
 * Author: Rob Davis
 * Date: no date
 * Code version: no version
 * Availability: https://wheel.x-alt.com/book/1/chapter/11/page/10
 * 
 * Title: Server-side authorisation
 * Author: Rob Davis
 * Date: no date
 * Code version: no version
 * Availability: https://wheel.x-alt.com/book/1/chapter/11/page/12
 */

/**
 * Include configuration file
 */
require_once "config/config.php";

/**
 * Autoload required classes.
 * No need to include them each time... 
 * but, composer would be better option.
 */
spl_autoload_register(function ($class) {
    require_once "classes/" . $class . ".php";
});

/**
 * Dynamically set variables, aka variable variable.
 * Grab the array key and set it as a variable name.
 * Grab the key value and set it as a variable value,
 * for example, array like the one below:
 * ["action"=>"list","subject"=>"films","term"=>"new"]
 * will produce variables like those listed below:
 * $action = "list", $subject = "films", $term = "new"
 * and so on - saves time to redeclare variables each time.
 */
foreach ($_REQUEST as $key => $value) {
    $$key = sanitise($value);
}

/**
 * Check if action is empty.
 * This is more likely to be executed when request comes from
 * the angular post, put or delete request methods.
 */
if (empty($action)) {
    if ((($_SERVER["REQUEST_METHOD"] == "POST") || 
    ($_SERVER["REQUEST_METHOD"] == "PUT") || 
    ($_SERVER["REQUEST_METHOD"] == "DELETE")) && 
    (strpos($_SERVER["CONTENT_TYPE"], "application/json") !== true)) {
        // Decode php://input
        $phpinput = json_decode(file_get_contents("php://input"), true);
        // Same as on line 49.
        foreach ($phpinput as $key => $value) {
            $$key = sanitise($value);
        }
    }
}

/**
 * Create route (or actual action name)
 */
$route = $action . ucfirst($subject);

/**
 * Instantiate new AppRegistry class
 */
$app = new AppRegistry();

/**
 * Instantiate new Session class
 */
$session = Session::init();

/**
 * Tell the web browser to expect JSON data format
 */
header("Content-Type: application/json");

/**
 * Do the swiiitchiiing
 * @TODO find a better way to do this, if time permits
 */
switch ($route) {

    /**
     * List films by id, term, category, actor_id or show all films.
     */
    case "listFilms":
        // Instantiate JSONRecordSet
        $records = new JSONRecordSet;
        // Initial query
        $query = "
            SELECT 
                nfc_film.film_id,
                nfc_film.title,
                substr(nfc_film.description, 0, 100) AS short_description,
                nfc_film.description AS description,
                nfc_film.release_year,
                nfc_film.rental_duration,
                nfc_film.rental_rate,
                nfc_film.length,
                nfc_film.replacement_cost,
                nfc_film.rating,
                nfc_film.special_features,
                nfc_film.last_update,
                nfc_category.name AS category,
                nfc_category.category_id AS category_id,
                (SELECT name FROM nfc_language WHERE nfc_film.language_id = nfc_language.language_id) AS language,
                (SELECT name FROM nfc_language WHERE nfc_film.original_language_id = nfc_language.language_id) AS original_language
            FROM nfc_film 
                JOIN nfc_film_category ON nfc_film.film_id = nfc_film_category.film_id
                JOIN nfc_category ON nfc_film_category.category_id = nfc_category.category_id
        ";
        // Check if $id was set (back on line 50 or 64)
        if (isset($id)) {
            // Add a where clause to the initial query
            $query .= " WHERE nfc_film.film_id = ? LIMIT 1";
            // Get records and echo results
            echo $records->get($query, $subject, [$id]);
        }
        // Otherwise, check if $term was set
        else if (isset($term)) {
            // Add a where clause and order by to the initial query
            $query .= " WHERE (nfc_film.title LIKE ? OR nfc_category.name LIKE ?) ";
            $bindParams[] = "%$term%";
            $bindParams[] = "%$term%";
            if (isset($category)) {
                $query .= " AND nfc_film_category.category_id = ? ";
                $bindParams[] = $category;
            }
            $query .= " ORDER BY nfc_film.title ASC LIMIT 12";
            if (isset($limit) && $limit == "no") {
                $query = rtrim($query, " LIMIT 12");
            }
            // Check if offset was set, if so, add it to the initial query
            $query .= isset($offset) ? " OFFSET $offset" : "";
            // Get records and echo out results
            echo $records->get($query, $subject, $bindParams);
        }
        // Otherwise, check if $category was set
        else if (isset($category)) {
            // Add a where clause and order by to the initial query
            $query .= " WHERE nfc_film_category.category_id = ? ORDER BY nfc_film.last_update DESC LIMIT 12";
            if (isset($limit) && $limit == "no") {
                $query = rtrim($query, " LIMIT 12");
            }
            // Check if offset was set, if so, add it to the initial query
            $query .= isset($offset) ? " OFFSET $offset" : "";
            // Get records and echo out results
            echo $records->get($query, $subject, [$category]);
        }
        // Otherwise, check if $actor_id was set
        else if (isset($actor_id)) {
            // Join nfc_film_actor table and add WHERE clause and order by to the initial query
            $query .= "
                JOIN nfc_film_actor ON nfc_film.film_id = nfc_film_actor.film_id
                WHERE nfc_film_actor.actor_id = ? ORDER BY nfc_film.release_year DESC LIMIT 12
            ";
            if (isset($limit) && $limit == "no") {
                $query = rtrim($query, " LIMIT 12");
            }
            // Check if offset was set, if so, add it to the initial query
            $query .= isset($offset) ? " OFFSET $offset" : "";
            // Get records and echo out results
            echo $records->get($query, $subject, [$actor_id]);
        }
        // Otherwise, check if $language was set
        else if (isset($language)) {
            // Add a WHERE clause and ORDER BY to the initial query
            $query .= "WHERE nfc_film.original_language_id = ? OR nfc_film.language_id = ? ORDER BY nfc_film.release_year DESC LIMIT 12";
            // @todo pagination. not required now, less than 12 categoies inside db
            // Get records and echo out results
            echo $records->get($query, $subject, [$language, $language]);
        }
        // Otherwise, query DB with default filters/conditions
        else {
            // Add a default WHERE clause to the initial query
            $query .= " WHERE 1 ORDER BY nfc_film.last_update DESC LIMIT 12 ";
            if (isset($limit) && $limit == "no") {
                $query = rtrim($query, " LIMIT 12");
            }
            // Check if offset was set, if so, add it to the initial query
            $query .= isset($offset) ? " OFFSET $offset" : "";
            // Get records and echo out results
            echo $records->get($query, $subject);
        }

        // Break the switch statement
        break;
    
    /**
     * List categories
     */
    case "listCategories":
        // Instantiate JSONRecordSet
        $records = new JSONRecordSet;
        // Initial query
        $query = "
            SELECT name, category_id AS id
            FROM nfc_category
            WHERE 1
        ";
        // Get records and echo out results
        echo $records->get($query, $subject);
        // Break the switch statement
        break;

    /**
     * List languages
     */
    case "listLanguages":
        // Instantiate JSONRecordSet
        $records = new JSONRecordSet;
        // Initial query
        $query = "
            SELECT
                nfc_language.language_id AS id,
                nfc_language.name AS name
            FROM nfc_language
            WHERE 1
            GROUP BY nfc_language.language_id
            ORDER BY nfc_language.name ASC
        ";
        // Get records and echo out results
        echo $records->get($query, $subject);
        // Break the switch statement
        break;
    
    /**
     * List actors by film_id or show them all
     */
    case "listActors":
        // Instantiate JSONRecordSet class
        $records = new JSONRecordSet;
        // Initial query
        $query = "
            SELECT 
                nfc_actor.*,
                nfc_film_actor.film_id AS film_id
            FROM nfc_film_actor
                JOIN nfc_actor ON nfc_film_actor.actor_id = nfc_actor.actor_id
        ";

        // Check if $film_id was set
        if (isset($film_id)) {
            // Select actors that match the $film_id, group them by actor and order by actor's last name
            $query .= " WHERE film_id = ? GROUP BY nfc_actor.actor_id ORDER BY nfc_actor.last_name";
             // Get records and echo out results
            echo $records->get($query, $subject, [$film_id]);
        }

        // Otherwuse, select actor by $id
        else if (isset($id)) {
            // Select actor that matches $id, group by actor_id to avoid duplicates
            $query .= " WHERE nfc_actor.actor_id = ? GROUP BY nfc_actor.actor_id";
            // Get records and echo out results
            echo $records->get($query, $subject, [$id]);
        }

        // Otherwise, select all actors
        else {
            // Select up to 12 actors
            $query .= " WHERE 1 GROUP BY nfc_actor.actor_id ORDER BY nfc_actor.last_name ";
            if (!isset($limit) && $limit == "no") {
                $query .= "LIMIT 12";
            }
            // Check if offset was set, if so, add it to the initial query
            $query .= isset($offset) ? " OFFSET $offset" : "";
            // Get records and echo out results
            echo $records->get($query, $subject);
        }

        // Break the switch statement
        break;

    case "listNotes":

        /**
         * Instantiate JSONRecordSet class
         */
        $records = new JSONRecordSet();
        
        /**
         * Initial query
         */
        $query = "
            SELECT
                nfc_film.film_id AS film_id,
                nfc_user.username AS author,
                nfc_note.*
            FROM nfc_film
                JOIN nfc_note ON nfc_film.film_id = nfc_note.film_id
                JOIN nfc_user ON nfc_note.user = nfc_user.email
        ";

        /**
         * Select 12 notes for given user
         */
        if (!empty($session->get("user")) && isset($user_id)) {
            $query .= " WHERE 1 AND nfc_note.user = ? LIMIT 12 ";
            if (isset($limit) && $limit == "no") {
                $query = rtrim($query, " LIMIT 12");
            }
            $query .= isset($offset) ? " OFFSET $offset" : "";
            echo $records->get($query, $subject, [$user_id]);
        }

       /**
        * Select 12 notes for given film_id
        */
       else  if (isset($film_id)) {
            $query .= " WHERE nfc_film.film_id = ? LIMIT 12 ";
            if (isset($limit) && $limit == "no") {
                $query = rtrim($query, " LIMIT 12");
            }
            $query .= isset($offset) ? " OFFSET $offset" : "";
            echo $records->get($query, $subject, [$film_id]);
        }

        /**
         * Select 12 notes (for any movie)
         */
        else {
            $query .= " LIMIT 12 ";
            if (isset($limit) && $limit == "no") {
                $query = rtrim($query, "LIMIT 12");
            }
            $query .= isset($offset) ? " OFFSET $offset" : "";
            echo $records->get($query, $subject);
        }

        /**
         * Break the switch statement
         */
        break;
        
    /**
     * Login user
     */
    case "loginUser":
        // Reset session to null
        $session->set("user", null);
        // Check if $email and $password were set back on line 64
        if (isset($email) && isset($password)) {
            // Instantiate new JSONRecordSet class
            $records = new JSONRecordSet;
            // Initial query
            $query = "
                SELECT 
                    email,
                    username,
                    password
                FROM nfc_user
                WHERE email = ?
                LIMIT 1
            ";
            // Get records, decode them and store into $temp variable
            $temp = json_decode($records->get($query, $subject, [$email]),1);
            // Check if no errors returned
            if ($temp["status"] !== "error") {
                // Grab user details from the $temp variable
                $temp = $temp["user"]["result"][0];
                // Vertify password
                if (password_verify($password, $temp['password'])) {
                    // Set new user session
                    $session->set("user", [
                        // Set email
                        "email" => $temp["email"],
                        // Set username
                        "username" => $temp["username"]
                    ]);
                    // Encode array and echo out response
                    echo json_encode([
                        "status" => "success",
                        "message" => "Logged in successfully",
                        "user" => $session->get("user")
                    ]);
                } 
                // Otherwise, if password is not verified
                else {
                    // Set unathorised request
                    header("Content-Type: application/json", true, 401);
                    // Encode array and echo out response
                    echo json_encode([
                        "status" => "error",
                        "message" => "Incorrect username or password",
                    ]);
                }
            }
            // Otherwise, if query returned errors
            else {
                // Set unathorised request
                header("Content-Type: application/json", true, 401);
                // Encode array and echo out response
                echo json_encode([
                    "status" => "error",
                    "message" => "This user does not exists"
                ]);
            }
        }
        // Break the switch statement
        break;

    /**
     * Check if user is already logged in
     * and echo out session data
     */
    case "checkUser":
        // Check if session user is not empty
        if (!empty($session->get("user"))) {
            // Encode array and echo out response
            echo json_encode([
                "status" => "success",
                "message" => "Logged in successfully",
                "user" => $session->get("user")
            ]);
        } 
        // Otherwise, user is not logged in
        else {
            // Encode array and echo out response
            echo json_encode([
                "status" => "error",
                "message" => "User is not logged in"
            ]);
        }
        // Break the switch statement
        break;

    /**
     * Logout user
     */
    case "logoutUser":
        // Check if user session was set
        if (!empty($session->get("user"))) {
            // Set user session to null
            $session->set("user", null);
            // Encode array and echo out response
            echo json_encode([
                "status" => "success",
                "message" => "Logged out successfully",
            ]);
        } 
        // Otherwise, user was not logged in
        else {
            // Encode array and echo out response
            echo json_encode([
                "status" => "error",
                "message" => "No user is currently logged in"
            ]);  
        }
        // Break the switch statement
        break;

    /**
     * Add note
     * This is a bit messy.
     * Whereas angular sends object to the backend, the testing page do not, it just passes params that are not wrapped inside any object
     * therefore this case needs to check for either object that contains note details or email. In production mode this case should be improved.
     */
    case "addNote":
        if (!empty($session->get("user")) && (isset($note) || isset($email))) {
            if (isset($note)) {
                $note = json_decode(htmlspecialchars_decode($note));
            }
            $user = $session->get("user");

            if ((isset($note->email) && $note->email === $user["email"]) || ($email === $user["email"])) {
                $records = new JSONRecordSet;
                $date = new DateTime();

                $query = "
                    INSERT INTO nfc_note (user, film_id, comment, lastupdated)
                    VALUES (?,?,?,?)
                ";

                echo json_encode($records->insert($query, $subject, [
                    isset($note->email) ? $note->email : $email,
                    isset($note->film_id) ? $note->film_id : $film_id,
                    isset($note->comment) ? $note->comment : $comment,
                    $date->format("Y-m-d H:i:s")
                ]));
            } 
        }
        else {
            header("Content-Type: application/json", true, 401);
            echo json_encode([
                "status" => "error",
                "message" => "Action denied. Please login to add notes."
            ]);
        }
        // Break switch statement
        break;

    /**
     * Update note
     */
    case "updateNote":
        if (!empty($session->get("user")) && isset($film_id) && isset($user) && isset($comment)) {
            $records = new JSONRecordSet;

            $query = "
                UPDATE nfc_note
                SET comment = ?
                WHERE user = ? AND film_id = ?
            ";

            echo json_encode($records->update($query, $subject, [$comment,$user,$film_id]));
        }
        else {
            // Encode array and echo out not found response
            header("Content-Type: application/json", true, 404);
            echo json_encode([
                "status" => "error",
                "message" => "You are not authorised to update this note."
            ]);
        }

        // Break switch statement
        break;
    
    /**
     * Delete note
     */
    case "deleteNote":

        if (!empty($session->get("user")) && isset($film_id) && isset($user)) {
            $records = new JSONRecordSet;

            $query = "
                DELETE FROM nfc_note 
                WHERE user = ? AND film_id = ?
            ";

            echo json_encode($records->delete($query, $subject, [$user, $film_id]));
        }
        else {
            // Encode array and echo out not found response
            header("Content-Type: application/json", true, 404);
            echo json_encode([
                "status" => "error",
                "message" => "You are not authorised to delete this note."
            ]);
        }

        break;
    
    /**
     * Default - in case $route does not match any condition
     */
    default:
        // Encode array and echo out not found response
        header("Content-Type: application/json", true, 404);
        echo json_encode([
            "status" => "error",
            "message" => "$route endpoint does not exists."
        ]);
    
} // end of switch statement

/**
 * Sanitise input
 * @param String $input
 * @return String $input
 */
function sanitise ($input) {
    // Encode special characters
    $input = htmlspecialchars($input);
    // Filter string
    $input = filter_var($input, FILTER_SANITIZE_STRING);
    // Return sanitised string
    return $input;
}