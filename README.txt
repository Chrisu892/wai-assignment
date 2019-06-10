Readme

General points

1. This web application uses AngularJS version 1.7.8.
2. Stylesheets in this web application have been written in SASS.
3. Two new directories have been added to resources:
    -  Images - to store images used within the web application.
    -  Partials - to store SCSS partials.
4. The js/partials directory has been renamed to includes. Since AngularJS uses ng-include directive to include html, it made more sense to the author to call it includes.
5. In routerProvider configuration, angular has a templateUrl property. Also, the AngularJS docs use the file.template.html naming convention. Therefore, it made more sense to the author to add a new directory to js called templates. Also, this would keep files organised as the application grows.
6. Logs directory have been added to server. Its empty, but should store error logs if unexpected errors occur.
7. Testing page opens in new tab. Please use the web browser "back" button to return back to the testing.php.