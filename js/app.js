/**
 * IIFE for angular scope
 */
(function () {
    "use strict";

    /**
     * Create new angular application
     */
    var app = angular.module("app", [
        "ngRoute", // Pass ngRoute as dependency
        "ngCookies", // Pass ngCookies as dependency
        "ngSanitize", // Pass ngSanitize as dependency
        "ngAnimate" // Pass ngAnimate as dependency
    ]);

    /**
     * Configure the app
     */
    app.config([
        "$routeProvider", // Inject built-in $routeProvider
        "$locationProvider", // Inject built-in $locationProvider
        function ($routeProvider, $locationProvider) {

            $locationProvider.html5Mode(true);

            $routeProvider
                .when("/film/:id", { // Chain the call to $routeProvider.when - it will execute if URL matched the pattern
                    templateUrl: "js/templates/film.html", // HTML template for given URL
                    controller: "FilmController" // Angular controller that contains logic for given path
                })
                .when("/terms", {
                    templateUrl: "js/templates/terms.html"
                })
                .when("/credits", {
                    templateUrl: "js/templates/credits.html"
                })
                .when("/privacy", {
                    templateUrl: "js/templates/privacy.html"
                })
                .otherwise({ // Chain the call to $routeProvider.when - it will run if route does not match any pattern
                    redirectTo: "/", // Redirection path
                    controller: "FilmsController" // Angular controller that contains logic for given path
                });
        }
    ]);

    /**
     * Index (Homepage) Controller
     * Extends the app module instantiated on line 17.
     * The approach to define controllers is based on the Angular.js documentation.
     * This controller handles the logic for the head and footer of the page.
     */
    app.controller("IndexController", // Name of the controller
        // Controller's function that receives angular variable $scope and dataService
        function ($scope, dataService) {
            // Initialise scope.data as an empty object.
            // Angular does not properly mutate primitive data types, 
            // therefore storing data into object fixes this issue.
            $scope.data = {};

            // Make the call to dataService method getMeta() passing no arguments
            // and attached chained method then()
            dataService.getMeta().then(
                // Function that will be executed when call to dataService.getMeta() 
                // returns successful response or is resolved.
                function (response) {
                    // Iterate through response data assign any properties dynamically
                    // No need to manually declare data properties.
                    for (var value in response) {
                        $scope.data[value] = response[value];
                    }
                },
                // Function that will be execute when call to dataService.getMeta()
                // returns error or is rejected.
                function (error) {
                    // In production this should be stored in the error log of some sort.
                    console.log(error);
                }
            );
    });

    /**
     * Films Controller
     * Extends the app module instantiated on line 17.
     * This controller handles the logic for the films listing on the homepage.
     */
    app.controller("FilmsController", 
        // Controller's function that receives angular $scope and $cookies variables and dataService
        function ($scope, dataService, $cookies) {

            // This JavaScript code removes class "locked" from the body.
            // This class is added to the body when user opens the film details pane.
            document.querySelector("body").classList.remove("locked");

            // Instantiate scope.data as an object with default parameters
            $scope.data = {
                action: "list", // Name of action that this controller handles
                subject: "films", // The subject of this controller
                loadMore: true, // By default, the "load more" button is enabled
                selectedCategory: null, // By default no category is selected
                loadedCategories: null, // By default no categories are loaded
                count: 0, // Films count is set to 0 by default
                films: [], // Films array is empty by default
                offset: 0, // Offset is set to 0 by default
                class: "main__image--cinema", // The main image background is a default cinema.jpg
                term: null, // Default search term
                loading: false
            };

            /**
             * Attach loadMore to the $scope and define function that receives no parameters.
             * This function will handle "load more" functionality
             */
            $scope.loadMore = function () {
                // Increment the $scope.data.offset by 12
                $scope.data.offset += 12;

                // Define the data object and set action, subject and offset properties
                var data = {
                    action: $scope.data.action, // Action passed to the backend
                    subject: $scope.data.subject, // Subject passed to the backend
                    offset: $scope.data.offset  // Offset passed to the backend
                }

                // Check if $scope.data.term is not null
                if ($scope.data.term) {
                    // Set term property in the data object
                    data.term = $scope.data.term;
                }

                // Check if $scope.data.selectedCategory is not null
                if ($scope.data.selectedCategory) {
                    // Set category ID in the data object
                    data.category = $scope.data.selectedCategory;
                }

                // Call to getFilms function to retrieve films
                // Pass data object as parameter.
                getFilms(data);
            };

            /**
             * Attach searchFilms to the $scope and define function that receives String as a parameter.
             * This function will handle search functionality
             */
            $scope.searchFilms = function (term) {
                // This is a new search that will return new data set,
                // therefore the film count must be reset to 0
                $scope.data.count = 0;
                // and array must be cleared out
                $scope.data.films = [];
                // term must be set out
                $scope.data.term = term;
                // and offset reset to 0
                $scope.data.offset = 0;

                // Check if selected category is empty
                if ($scope.data.selectedCategory === "") {
                    // Set it to null
                    $scope.data.selectedCategory = null;
                }

                // Prepare data
                var data = {
                    action: "list", // action name passed to the backend
                    subject: "films", // subject passed to the backend
                    offset: $scope.data.offset, // offset, if any, passed to the backend
                    term: $scope.data.term // search term passed to the backend
                };

                // Check if $scope.data.selectedCategory is not null
                if ($scope.data.selectedCategory) {
                    // Set category property in data object
                    data.category = $scope.data.selectedCategory;
                }

                // Call to getFilms function to retrieve films
                // Pass data object as parameter.
                getFilms(data);
            };

            /**
             * Attach loadCategories to the $scope and define function that receives no parameters.
             * Method to load categories from the database or cookies
             */
            $scope.loadCategories = function () {
                // Check if categories cookie was set
                if ($cookies.get("categories")) {
                    // Get the cookie and assign it to the scope's data object
                    $scope.data.loadedCategories = JSON.parse($cookies.get("categories"));
                    // Return true to prevent further execution of the code
                    return;
                }

                // Fetch categories from the database
                dataService.get({ 
                    action: "list", // Pass action as first parameter
                    subject: "categories" // Pass subject as second parameter
                }).then(
                    // Function that will be executed when call to dataService.get() 
                    // returns successful response or is resolved.
                    function (response) {
                        // Put returned categories object into cookies
                        $cookies.putObject("categories", response.categories.result);
                        // Set $scope.data.loadedCategories property to returned object
                        $scope.data.loadedCategories = response.categories.result;
                    },
                    // Function that will be execute when call to dataService.getMeta()
                    // returns error or is rejected.
                    function (error) {
                        // In production this should be stored in the error log of some sort.
                        console.log(error);
                    }
                );

            };

            /**
             * Attach $watch("data.selectedCategories") to the $scope and define function that receives no parameters.
             * This function will handle films categories functionality and background image change
             */
            $scope.$watch("data.selectedCategory", function () {

                // Check if category is not null
                if ($scope.data.selectedCategory !== null) {
                    
                    /**
                     * This forEach iteration is executed in order to get the name of selected category.
                     * By default, categories are passed as numbers, however, to get the proper name of the image,
                     * the full name of category must be retrieved. This optional feature of the app.
                     */
                    $scope.data.loadedCategories.forEach(function (category) {
                        // Check if category.id matches the $scope.data.selectedCategory
                        if (category.id == $scope.data.selectedCategory) {
                            // Set $scope.data.class property to appropriate class name. Convert the category name to lowercase.
                            $scope.data.class = "main__image--" + category.name.toLowerCase();
                            // Return true to stop the loop
                            return;
                        }
                    });

                                    // Call to searchFilms function to retrieve matching films
                // Pass $scope.data.term as the only parameter.
                $scope.searchFilms($scope.data.term);
                }
                else {
                    // If category is not selected then reset to default background image
                    $scope.data.class = "main__image--cinema";
                }
            });

            /**
             * Variable getFilms which is a function
             * @param {Object} data - contains required parameters
             * This function wraps the call to dataService.get() method and handles the returned response.
             * It was wrapped into function as it is common functionality for $scope.loadMore and $scope.searchFilms
             */
            var getFilms = function (data) {
                // Make the call to dataService method get() passing one argument which is data object
                // and attach chained method then()
                dataService.get(data).then(
                    // Function that will be executed when call to dataService.getMeta() 
                    // returns successful response or is resolved.
                    function (response) {
                        // Increment the films count by the count returned from the call
                        $scope.data.count += response.films.count;
                        // Disable "load more" button if count is less than 12
                        $scope.data.loadMore = response.films.count < 12 ? false : true;
                        // Loop through returned films
                        for (var index in response.films.result) {
                            // Wraps the block of code into try & catch in case date.toLocaleDateString does not 
                            // work in older web browsers
                            try {
                                // Create new instance of Date and pass last_update as parameter
                                var date = new Date(response.films.result[index].last_update);
                                // Convert last_update to local date string
                                response.films.result[index].last_update = date.toLocaleDateString("en-GB", {
                                    weekday: "long", year: "numeric", month: "long", day: "numeric" 
                                });
                            } 
                            catch (error) {
                                // In production this error should be stored in error log file of some sort
                                console.log(error);
                            }

                            // Push film into $scope.data.films array
                            $scope.data.films.push(response.films.result[index]);
                        }
                    },
                    // Function that will be executed when call to dataService.get()
                    // returns error or is rejected.
                    function (error) {
                        // In production this error should be stored in error log file of some sort
                        console.log(error);
                    }
                );
            };

            if ($scope.data.films.length === 0) {
                // Call to getFilms function to retrieve list of films
                // Executed when page is reloaded
                getFilms({ 
                    action: $scope.data.action, // Action name send to the backend
                    subject: $scope.data.subject // Subject name send to the backed
                });
            }
    });

    /**
     * Film controller
     * Extends the app module instantiated on line 17.
     * This controller handles the logic for single film listing on the homepage.
     */
    app.controller("FilmController", 
        // Controller's function that receives angular $scope, $routeParams and $location variables and dataService
        function ($scope, dataService, $routeParams, $location) {

            // When film is selected, lock the scrolling behaviour on the body
            document.querySelector("body").classList.add("locked");

            // Instantiate scope.data as an object with default parameters
            $scope.data = {
                film: null, // set film property to null
                actors: [], // set actors property as empty array
                activeTab: "info", // the name of active tab,
                class: "main__image--cinema" // the background image for the preview - it's the same as category it belongs to
            };

            /**
             * Attach toggleTab to the $scope and define function that receives String tabName as a parameter.
             * This function will handle tabs switching.
             */
            $scope.toggleTab = function (tabName) {
                $scope.data.activeTab = tabName;
            };

            /**
             * Attach close to the $scioe and define function that receives no parameters.
             * This function will enable users to close the film pane.
             */
            $scope.close = function () {
                // Remove "locked" class from the body to enable scrolling
                document.querySelector("body").classList.remove("locked");
                // Set path to the index.html
                $location.path("index");
            };

            // @TODO: at this point, application could check if $cookies contain the film. 
            // If so, load from cookie, otherwise, fetch from network.

            // Make the call to dataService method get() passing one argument which is a data object
            // and attach chained method then()
            dataService.get({ 
                action: "list", // action name
                subject: "films", // subject name
                id: $routeParams.id // the film id
            }).then(
                // Function that will be executed when call to dataService.get() 
                // returns successful response or is resolved.
                function (response) {
                    try {
                        // Create new instance of Date and pass last_update as parameter
                        var date = new Date(response.films.result[0].last_update);
                        // Convert last_update to local date string
                        response.films.result[0].last_update = date.toLocaleDateString('en-GB', {
                            weekday: "long", year: "numeric", month: "long", day: "numeric" 
                        });
                    }
                    catch (error) {
                        // In production this error should be stored in error log file of some sort
                        console.log(error);
                    }
                    // Set the $scope.data.film property to the returned film
                    $scope.data.film = response.films.result[0];
                    $scope.data.class = "main__image--" + response.films.result[0].category.toLowerCase();
                },
                // Function that will be executed when call to dataService.get()
                // returns error or is rejected.
                function (error) {
                    // In production this error should be stored in error log file of some sort
                    console.log(error);
                }
            );

            // Make the call to dataService method get() passing one argument which is data object
            // and attach chained method then()
            dataService.get({ 
                action: "list", // action name
                subject: "actors", // subject name
                film_id: $routeParams.id // selected film ID
            }).then(
                // Function that will be executed when call to dataService.get() 
                // returns successful response or is resolved.
                function (response) {
                    // Set $scope.data.actors property to response.actors.result
                    $scope.data.actors = response.actors.result;
                },
                // Function that will be executed when call to dataService.get()
                // returns error or is rejected.
                function (error) {
                    // In production this error should be stored in error log file of some sort
                    console.log(error);
                }
            );
        }
    );

    /**
     * Notes Controller
     * Extends the app module instantiated on line 17.
     * This controller handles the logic for notes listed on the films pane.
     */
    app.controller("NotesController", 
        // Controller's function that receives angular $scope, $routeParams and $cookies variables and dataService
        function ($scope, dataService, $routeParams, $cookies) {

            // Instantiate scope.data as an object with default parameters
            $scope.data = {
                count: 0, // default notes count is 0
                notes: [], // default notes array is empty
                user: null, // by default, there is not user
                showForm: false, // add note form is hidden by default
                comment: "", // comment is empty by default
                editedComment: "", // to store edited comment
                editedCommentError: "", // to store error for edited comment
                error: null, // no error messages by default
                userAddedNote: false // to hold the reference to whether user added a note or not
            };

            $scope.$watch("data.comment", function () {
                $scope.data.error = $scope.data.comment.length === 0 ? "This field is required." : null;
            });

            $scope.$watch("data.editedComment", function () {
                $scope.data.editedCommentError = $scope.data.editedComment.length === 0 ? "This field is required." : null;
            });

            /**
             * Attach toggleFom to the $scope and define function that receives Number index as a parameter.
             * This function will handle switching the visibility of the update note form.
             */
            $scope.toggleForm = function (index) {
                // Toggle visibility of the form on/off
                $scope.data.showForm = !$scope.data.showForm;
                // Set the comment to the note's comment
                $scope.data.editedComment = $scope.data.notes[index].comment;
            };

            /**
             * Attach updateNote to the $scope and define function that receives Number index as a parameter.
             * This function will handle updating the note when update button is pressed.
             */
            $scope.updateNote = function (index) {
                // Check if the comment length is not 0
                if ($scope.data.editedComment.length !== 0) {
                    // Make the call to dataService method get() passing one argument which is data object
                    // and attach chained method then()
                    dataService.post({ 
                        action: "update", // action name
                        subject: "note", // subject name
                        comment: $scope.data.editedComment, // comment value
                        user: $scope.data.user.email, // user's email address (for verification against server-side session)
                        film_id: $routeParams.id // the film id
                    }).then(
                        // Function that will be executed when call to dataService.get() 
                        // returns successful response or is resolved.
                        function () {
                            // Hide the update note form.
                            $scope.data.showForm = !$scope.data.showForm;
                            // Update the comment 
                            $scope.data.notes[index].comment = $scope.data.editedComment;
                        },
                        // Function that will be executed when call to dataService.get()
                        // returns error or is rejected.
                        function (error) {
                            // In production this error should be stored in error log file of some sort
                            console.log(error);
                            $scope.data.error = error.message;
                        }
                    );
                }
                else {
                    // Set the error message.
                    $scope.data.error = "Note cannot be empty.";
                }
            };

            /**
             * Attach deleteNote to the $scope and define function that receives index as a parameter.
             * This function will handle deleting the note when delete button is pressed.
             */
            $scope.deleteNote = function (index) {
                // Show the confirm dialogue
                if (!confirm("Are you sure you want to delete this comment?")) {
                    // If pressed cancel, then return true to prevent execution of the code below.
                    return;
                }

                // Make the call to dataService method get() passing one argument which is data object
                // and attach chained method then()
                dataService.post({
                    action: "delete", // action name
                    subject: "note", // subject name
                    film_id: $scope.data.notes[index].film_id, // the film id from the note object
                    user: $scope.data.user.email // user's email address (for backend verification)
                }).then(
                    // Function that will be executed when call to dataService.get() 
                    // returns successful response or is resolved.
                    function () {
                        // Remove note from the notes array
                        $scope.data.notes.splice(index,1);
                        // Decrease number of notes by 1
                        $scope.data.count--;
                        // Show form again
                        $scope.data.userAddedNote = false;
                    },
                    // Function that will be executed when call to dataService.get()
                    // returns error or is rejected.
                    function (error) {
                        // In production this error should be stored in error log file of some sort
                        console.log(error);
                        $scope.data.error = error.message;
                    }
                );
            };

            /**
             * Attach addNote to the $scope and define function that receives no parameters.
             * This function will handle adding the note when add button is pressed.
             */
            $scope.addNote = function () {
                // Check if comment length is not 0 and email and film id is not null
                if ($scope.data.comment.length !== 0 && $scope.data.user.email && $routeParams.id) {
                    // Define the noteData object and set action, subject and note properties
                    var noteData = {
                        action: "add", // action name
                        subject: "note", // subject name
                        note: JSON.stringify({ // stringify the object
                            email: $scope.data.user.email, // email address of the user (for verification)
                            film_id: $routeParams.id, // the film id
                            comment: $scope.data.comment // comment
                        })
                    };

                    // Make the call to dataService method post() passing one argument which is data object
                    // and attach chained method then()
                    dataService.post(noteData).then(
                        // Function that will be executed when call to dataService.get() 
                        // returns successful response or is resolved.
                        function () {
                            // Grab the noteData.note from previously declared variable
                            noteData = JSON.parse(noteData.note);
                            // Logged in user is the note author
                            noteData.isAuthor = true;
                            // Add note author to the noteData
                            noteData.author = $scope.data.user.username;
                            // The time the note was added
                            noteData.lastupdated = "just now";
                            // Push the newly created not to the $scope.data.notes array
                            $scope.data.notes.push(noteData);
                            // Set $scope.data.comment property to empty string
                            $scope.data.comment = "";
                            // Increase the number of notes by 1
                            $scope.data.count++;
                            // Hide the form
                            $scope.data.userAddedNote = true;
                        },

                        // Function that will be executed when call to dataService.get()
                        // returns error or is rejected.
                        function (error) {
                            // In production this error should be stored in error log file of some sort
                            console.log(error);
                            $scope.data.error = error.message;
                        }
                    );
                }
                else {
                    $scope.data.error = "Please enter a note.";
                }
            };

            // Check if user object is set in the $cookies
            if ($cookies.get("user")) {
                // Get the user cookie, parse it and set it as $scope.data.user property
                $scope.data.user = JSON.parse($cookies.get("user"));
            }

            // Check if $scope.data.user is not null
            if ($scope.data.user) {
                // Make the call to dataService method post() passing one argument which is data object
                // and attach chained method then()
                dataService.get({ 
                    action: "list", // action name
                    subject: "notes", // subject name
                    film_id: $routeParams.id // film id
                }).then(
                    // Function that will be executed when call to dataService.get() 
                    // returns successful response or is resolved.
                    function (response) {
                        // Set the notes count to the count returned in the response
                        $scope.data.count = response.notes.count;
                        // For each index in response.note.result
                        for (var index in response.notes.result) {
                            try {
                                // Create new instance of Date and pass last_update as parameter
                                var date = new Date(response.notes.result[index].lastupdated);
                                // Convert last_update to local date string
                                response.notes.result[index].lastupdated = date.toLocaleDateString('en-GB', { 
                                    weekday: "long", year: "numeric", month: "long", day: "numeric" 
                                });

                                // Check if the logged in user is the author of the note
                                if ($scope.data.user.email === response.notes.result[index].user) {
                                    // Set the isAuthor property in response.notes.results to true
                                    response.notes.result[index].isAuthor = true;
                                    $scope.data.userAddedNote = true;
                                }
                            }
                            // Catch unexpected errors
                            catch (error) {
                                // In production this error should be stored in error log file of some sort 
                                console.log(error);
                            }
                        }

                        // Set $scope.data.notes to the returned and modified response.notes.result
                        $scope.data.notes = response.notes.result;
                    },
                    // Function that will be executed when call to dataService.get() returns error or is rejected.
                    function (error) {
                        // In production this error should be stored in error log file of some sort
                        console.log(error);
                    }
                );
            };
        }
    );

    /**
     * Authentication Controller
     * Extends the app module instantiated on line 17.
     * This controller handles the user authentication.
     */
    app.controller("AuthController", 
        // Controller's function that receives angular $scope and $cookies variables and dataService
        function ($scope, dataService, $cookies) {

            // Instantiate scope.data as an object with default parameters
            $scope.data = {
                email: null, // user email address entered into input field
                password: null, // user password entered into input field
                error: null, // error message
                showForm: false, // set form visibility to hidden
                user: $cookies.get("user") // check if user cookie has been set
                    ? JSON.parse($cookies.get("user")) // get the cookie and parse it
                    : null // otherwise set user to null
            };

            /**
             * Attach login to the $scope and define function that receives no parameters.
             * This function will handle user login when login button was pressed.
             */
            $scope.login = function () {
                // Check if email address and password were entered
                if ($scope.data.email && $scope.data.password) {
                    // Make the call to dataService method post() passing one argument which is data object
                    // and attach chained method then()
                    dataService.post({
                        action: "login", // action name
                        subject: "user", // subject name
                        email: $scope.data.email, // email address
                        password: $scope.data.password // user password
                    }).then(
                        // Function that will be executed when call to dataService.post() 
                        // returns successful response or is resolved.
                        function (response) {
                            // Set the $scope.data.user property to returned response.data.user
                            $scope.data.user = response.data.user;
                            // Put the user cookie into local storage
                            $cookies.putObject("user", response.data.user);
                            // Set $scope.data.email and $scope.data.password to null
                            $scope.data.email = $scope.data.password = null;
                            // Hide to login form
                            $scope.data.showForm = false;
                        },
                        // Function that will be executed when call to dataService.post()
                        // returns error or is rejected.
                        function (error) {
                            // Set $scope.data.error to returned error message
                            $scope.data.error = error.data.message;
                        }
                    );
                }
                else {
                    // Set $scope.data.error to returned error message
                    $scope.data.error = "Incorrect username or password";
                }
            };

            /**
             * Attach logout to the $scope and define function that receives no parameters.
             * This function will handle the user logout when logout button was pressed.
             */
            $scope.logout = function () {
                // Check if user cookie was set.
                if ($cookies.get("user")) {
                    // Ask user to confirm action
                    if (!confirm("Are you sure you want to logout?")) {
                        // If user cancelled the action, then return true to stop exectution of the code
                        return;
                    }
                    // Make the call to dataService method post() passing one argument which is data object
                    // and attach chained method then()
                    dataService.post({
                        action: "logout", // action name
                        subject: "user" // subject name
                    }).then(
                        // Function that will be executed when call to dataService.post() 
                        // returns successful response or is resolved.
                        function () {
                            // Remove the user cookie
                            $cookies.remove("user");
                            // Set the $scope.data.user property to null
                            $scope.data.user = null;
                        },
                        // Function that will be executed when call to dataService.post()
                        // returns error or is rejected.
                        function (error) {
                            // In production this error should be stored in error log file of some sort
                            console.log(error);
                        }
                    );
                }
            };

            /**
             * Attach toggleForm to the $scope and define function that receives no parameters.
             * This function will toggle the visibility of the login form on and off.
             */
            $scope.toggleForm = function () {
                // Set the visibility of the form on or off.
                $scope.data.showForm = !$scope.data.showForm;
            };
        }
    );
}());

/**
 * IIFE for the header background image change on scroll
 */
(function () {
    "use strict";

    // Get the reference to page header
    var header = document.getElementById("header");
    // Set the scroll position at which the background colour should change to darker
    var heroHeight = 200;
    // Define variable to store the scroll position
    var scrolled;

    // Attach scroll event listener to the window object
    window.addEventListener("scroll", function () {
        // Assign scrollY to the scrolled variable
        scrolled = window.scrollY;
        // Check if page hero is out of viewport
        if (scrolled >= heroHeight) {
            // Add class that will change the background colour of the header
            header.classList.add("header--darken");
        } 
        else {
            // Remove class that changed the background colour of the header
            header.classList.remove("header--darken");
        }
    });
}());