'use strict';

angular.
  module('framePreview').
  component('framePreview', {
    templateUrl: 'scripts/video-editor/video-editor.template.html',
    controller: ['$http', '$scope', function framePreviewController($http, $scope) {
      $http.get('get_frames_json.php').then(res => {
        this.frames = res.data;
        console.log('this.frames=', this.frames)
        // this.frames.arrFrames = this.frames['arrayFrames'];
        console.log('this.frames.arrFrames = ', this.frames.arrFrames);
        // for (let i = 0; i < this.frames.arrFrames.length; i++) {
        //   this.frames.arrFrames[i].isSelected = this.frames.arrFrames[i].isSelected;
        // }
      });


    }
  ]});
