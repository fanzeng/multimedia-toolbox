'use strict';

angular.
  module('framePreview').
  component('framePreview', {
    templateUrl: 'scripts/frame-preview/frame-preview.template.html',
    controller: function framePreviewController($http) {
      var self = this;
      $http.get('get_frames_json.php').then(function(response) {
        self.frames = response.data;
      });
    }
  });
