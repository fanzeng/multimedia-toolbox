'use strict';

angular.
  module('videoCreator').
  component('frame-preview', {
    templateUrl: 'frame-preview/frame-preview.template.html',
    controller: function framePreviewController($http) {
      var self = this;
      

      $http.get('phones/phones.json').then(function(response) {
        self.frameURL = response.data;
      });
    }
  });
