;(function() {

    'use strict';

    /**
     * you might add this global template path
     * myApp.config(function(paginationTemplateProvider) {
     *     paginationTemplateProvider.setPath('path/to/dirPagination.tpl.html');
     * });
     */

    var packages = [
        'ngMessages',
        '720kb.datepicker',
        'ngTagsInput',
        'angularUtils.directives.dirPagination',
        'ngFileUpload',
    ];

    angular.module('documentsCatalogApp', packages)
        .config(interpolationConfig)
        .factory('tocTreeService', tocTreeService)
        .factory('documentsService', documentsService)
        .controller('pageController', pageController)
        .controller('navController', navController)
        .directive('toctree', tocTreeDirective);

    interpolationConfig.$inject = ['$interpolateProvider'];
    function interpolationConfig($interpolateProvider) { 
        $interpolateProvider
            .startSymbol('<%').endSymbol('%>');
    }

/*
    .config(function($routeProvider, $locationProvider) {
  $routeProvider
   .when('/Book/:bookId', {
    templateUrl: 'book.html',
    controller: 'BookController',
    resolve: {
      // I will cause a 1 second delay
      delay: function($q, $timeout) {
        var delay = $q.defer();
        $timeout(delay.resolve, 1000);
        return delay.promise;
      }
    }
  })
  .when('/Book/:bookId/ch/:chapterId', {
    templateUrl: 'chapter.html',
    controller: 'ChapterController'
  });
*/

    routingConfig.$inject = ['$routeProvider', '$locationProvider'];
    function routingConfig($routeProvider, $locationProvider) {
        $routeProvider.
            when('/contents', {
                templateUrl: 'book.html',
                controller: 'documentsController'
            });
    }

    pageController.$inject = [
        'tocTreeService', 
        'documentsService', 
        '$scope', 
        '$timeout', 
        'Upload',
    ];

    function pageController(tocTreeService, documentsService, $scope, $timeout, Upload) {

        var vm = this;

        vm.documents = [];
        vm.createdDocuments = [];
        vm.documentsCount = 0;
        // vm.currentPage = 1;
        vm.breadcrumbs = [];        
        vm.newDocumentMeta = {};
        vm.currentDirectory = tocTreeService.getActiveNode();

        // reafctor: pass a reference to the file and operate on that
        vm.addNewDocument = function() {

            var newDocument = {
                title: vm.newDocumentMeta.title,
                comment: vm.newDocumentMeta.comment,
                date: vm.newDocumentMeta.date,
                tags: vm.newDocumentMeta.tags,
                file: vm.file,
                directory_id: vm.currentDirectory.id
            };

            vm.file.upload = Upload.upload({
                // 1. how to automatically add the <base> ?
                // 2. how to automatically add the API_PREFIX?
                url: '/carlos/api/v1.0/documents/new',
                data: newDocument,
            });

            vm.file.upload.then(
                function(response) {
                    $timeout(function() {
                        
                        resetNewDocumentForm();
                        getDocumentsPage(vm.currentDirectory, vm.currentPage);
                        
                        /*
                        // push only titles
                        vm.createdDocuments.push({
                            title: response.data.title
                        });
                        */

                    });
                }, 
                function(response) {
                    if(response.status > 0) {
                        console.log(response); 
                    }
                    // $scope.errorMsg = response.status + ': ' + response.data;
                }, 
                function(evt) {
                    // Math.min is to fix IE which reports 200% sometimes
                    vm.file.progress = Math.min(100, parseInt(100.0 * evt.loaded / evt.total));
                }
            );

        }

        vm.deleteDocument = function(document) {

            documentsService.deleteDocument(document).then(function(response) {
                getDocumentsPage(vm.currentDirectory, vm.currentPage);
            }).catch(function(error) {
                console.log(error);
            });

        }

        vm.getTags = function(label) {
            return documentsService.getDocumentsTags(label);
        }

        // events / watches

        vm.pageChanged = function(newPage) {
            getDocumentsPage(vm.currentDirectory, newPage);
        }

        $scope.$watch(
            // expression to watch
            function() { return vm.currentDirectory; },            
            // change-handler
            function(newValue, oldValue) {
                // console.log(arguments);
                if(newValue && newValue.id) {
                    resetNewDocumentForm();
                    getDocumentsPage(newValue);
                    getBreadcrumbs(); 
                }
            }, 
            // enable the "deep watch" mode
            true
        );

        function getDocumentsPage(directory, page) {
            documentsService.getDocumentsByDirectory(directory, page).then(function(documentsData) {
                vm.documents = documentsData.documents;
                vm.documentsCount = documentsData.documentsCount;
            }).catch(function(error) {
                console.log(error);
            });
        }

        function getBreadcrumbs() {
            tocTreeService.getBreadcrumbs().then(function(breadcrumbs) {
                vm.breadcrumbs = breadcrumbs;
            }).catch(function(error) {
                console.log(error);
            });
        }

        function resetNewDocumentForm() {
            // reset view model
            vm.newDocumentMeta = {};
            vm.file = null;

            // reset form
            vm.newDocumentForm.$setPristine();
            vm.newDocumentForm.$setUntouched();
        }

    }

    documentsService.$inject = ['$http'];
    function documentsService($http) {

        function getDocumentsByDirectory(directory, page) {
            
            var urlFormat = '/carlos/api/v1.0/documents/directories/{0}/page/{1}';
            var url = String.format(urlFormat, directory.id, page || 1);
            
            return $http.get(url).then(function(response) {
                return response.data;
            });
        }

        function getDocumentsTags(label) {

            // According to ng-tags-input we gotta return the promise with response data
            
            var url = '/carlos/api/v1.0/documents/tags'; 
            return $http.get(url, {label: label});

        }

        function deleteDocument(document) {

            var url = '/carlos/api/v1.0/documents/delete';
            return $http.post(url, {id: document.id});

        }

        return {
            getDocumentsByDirectory: getDocumentsByDirectory,
            getDocumentsTags: getDocumentsTags,
            deleteDocument: deleteDocument
        }

    }

    navController.$inject = ['tocTreeService'];
    function navController(tocTreeService) {
        
        var vm = this;

        vm.toc = [];
        
        tocTreeService.getToc().then(function(response) {
            vm.toc = response;
        }).catch(function(error) {
            console.log(error);
        });

    }

    tocTreeService.$inject = ['$http'];
    function tocTreeService($http) {

        var activeNode = { 
            id: null,
            name: null 
        };

        function getActiveNode() {
            return activeNode;
        }

        function setActiveNode(node) {
            activeNode.id = node.id;
            activeNode.name = node.name;
        }

        function getToc() {
            return $http.get('/carlos/api/v1.0/documents/nav/toc').then(function(response) {
                return response.data;
            });
        }

        // REFACTOR: this (probably) doesn't belong here

        function getBreadcrumbs() {

            // refactor: this is the bad way of doing it

            var directoryId = activeNode.id;
            var url = '/carlos/api/v1.0/documents/nav/bc/directories/' + directoryId;

            return $http.get(url).then(function(response) {
                return response.data;
            });

        }

        return {
            getActiveNode: getActiveNode,
            setActiveNode: setActiveNode,
            getToc: getToc,
            getBreadcrumbs: getBreadcrumbs
        }

    };

    tocTreeDirective.$inject = ['tocTreeService'];
    function tocTreeDirective(tocTreeService) {

        // NOTE: if I refer to the directive as tree then it might 
        // be more appropriate to use '.tree-item' or '.tree-node'
        // classes

        var templateText = 
            '<ul class="accordion depth-<% depth %>">' +
                '<li ng-repeat="node in tree">' +
                    '<input type="checkbox" name="item-<% node.id %>" id="item-<% node.id %>" ng-if="!isLeaf(node)" />' +
                    '<label class="accordion-item label" for="item-<% node.id %>" ng-if="!isLeaf(node)">' +
                        '<% ::node.name %>' +
                    '</label>' +
                    '<a ng-click="activateLeaf(node)" ng-if="isLeaf(node)" class="accordion-item label" ng-class="{ active: isLeafActive(node) }">' +
                        '<% ::node.name %>' +
                    '</a>' +
                    '<toctree tree="node.children" depth="<% getDepth() + 1  %>"></toctree>' + 
                '</li>' + 
            '</ul>';

        return {
            replace: true,
            template: templateText,
            scope: {
                tree: '=',
                depth: '@'
            },
            link: function(scope) {
                
                scope.getDepth = function() {
                    return parseInt(scope.depth);
                }

                scope.isLeaf = function(node) {
                    return node.children.length == 0;
                }

                scope.isLeafActive = function(node) {
                    return node.id === tocTreeService.getActiveNode().id;
                }

                scope.activateLeaf = function(node) {
                    tocTreeService.setActiveNode(node);
                }

            }
        }
    }

})();


/*

angular.module('directive.loading', []).directive('loading',   ['$http' ,function ($http)
{
    return {
        restrict: 'A',
        link: function (scope, elm, attrs)
        {
            scope.isLoading = function () {
                return $http.pendingRequests.length > 0;
            };

            scope.$watch(scope.isLoading, function (v)
            {
                if(v){
                    elm.show();
                }else{
                    elm.hide();
                }
            });
        }
    };

}]);

*/