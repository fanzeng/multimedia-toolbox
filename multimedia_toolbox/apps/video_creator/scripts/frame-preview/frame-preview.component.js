'use strict';

angular.
  module('framePreview').
  component('framePreview', {
    templateUrl: 'scripts/frame-preview/frame-preview.template.html',
    controller: ['$http', function framePreviewController($http) {
      var self = this;
      $http.get('get_frames_json.php').then(function(response) {
        self.frames = response.data;
      });

      this.onclick_update_frames = function() {

        var post_data = $.param(self.frames);
        $http.post('set_frames_json.php', post_data, {
          url: 'set_frames_json.php',
          method: "POST",
          headers: {'Content-Type': 'application/x-www-form-urlencoded'}
        }).then(function(response) {
          console.log(response.data);
        }, function(response) {
          // console.log(response.data);
        });
      };
    }
  ]});
