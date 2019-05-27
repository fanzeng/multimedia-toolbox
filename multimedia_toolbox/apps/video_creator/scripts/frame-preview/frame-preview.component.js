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
        // console.log(this.frames['array_frames']);
        $http.post('set_frames_json.php', {"array_frames": self.frames['array_frames']}, {
          url: 'set_frames_json.php',
          method: "POST",
          headers: {'Content-Type': 'application/json'}
         
        }).then(function(response) {
          console.log(response);
        });
      };
    }
  ]});
