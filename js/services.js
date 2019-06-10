/**
 * IIFE for angular dataService scope
 */
(function () {
    "use strict";

    /**
     * Register new data service to angular module
     */
    angular.module("app").service("dataService", [
        "$q", // Dependency to handle async functions
        "$http", // Dependency to handle async requests

        // Define a constructor function and inject $q and $http parameters
        function ($q, $http) {

            /**
             * Server url - path to backend api
             * @var String
             */
            var serverUrl = "/wai-assignment/server/";

            /**
             * Meta url - path to the json file that contains app info
             * @var String
             */
            var metaUrl = "/wai-assignment/server/db/appInfo.json";

            /**
             * getMeta - service to get app info from appInfo.json
             * @return {Object}
             */
            this.getMeta = function () {
                // Define variable defer that will return a promise when called
                var defer = $q.defer();
                // Make the get call to the backend
                // Pass metaUrl and configuration object that contains cache: 1 as data returned from this promise won't change
                $http.get(metaUrl, { cache: 1 }).then(
                    // Success function that has response parameter 
                    function (response) {
                        // resolve the promise
                        defer.resolve(response.data)
                    },
                    // Error function that has response parameter
                    function (error) {
                        // Reject the promise
                        defer.reject(error);
                    }
                );
                // Return the promise
                return defer.promise;
            };

            /**
             * Method to get something from database
             * @param {Object} data
             */
            this.get = function (data) {
                // Check if action and subject was set
                if (data.action && data.subject) {
                    // Define variable defer that will return a promise when called
                    var defer = $q.defer();
                    // Pass serverUrl and configuration object
                    $http.get(serverUrl, { params: data, cache: 1 }).then(
                        // Success function that has response parameter 
                        function (response) {
                            // resolve the promise
                            defer.resolve(response.data);
                        },
                        // Error function that has response parameter
                        function (error) {
                            // Reject the promise
                            defer.reject(error);
                        }
                    );
                    // Return the promise
                    return defer.promise;
                }
            };

            /**
             * Method to post something into database
             * @param {Object} data
             */
            this.post = function (data) {
                // Define variable defer that will return a promise when called
                var defer = $q.defer();
                // Pass serverUrl and configuration object
                $http.post(serverUrl, data).then(
                    // Success function that has response parameter 
                    function (response) {
                        // Resolve the promise
                        defer.resolve(response);
                    },
                    // Error function that has response parameter
                    function (error) {
                        // Reject the promise
                        defer.reject(error);
                    }
                );
                // Return the promise
                return defer.promise;
            }
        }
    ]);
}());