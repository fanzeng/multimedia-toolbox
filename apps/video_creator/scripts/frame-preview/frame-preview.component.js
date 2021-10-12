'use strict';

angular.
  module('framePreview').
  component('framePreview', {
    templateUrl: 'scripts/frame-preview/frame-preview.template.html',
    controller: ['$http', '$scope', function framePreviewController($http, $scope) {
      $http.get('get_frames_json.php').then(res => {
        this.frames = res.data;
        console.log('this.frames=', this.frames)
        this.frames.arrFrames = this.frames['array_frames'];
        console.log('this.frames.arrFrames = ', this.frames.arrFrames);
        // for (let i = 0; i < this.frames.arrFrames.length; i++) {
        //   this.frames.arrFrames[i].isSelected = this.frames.arrFrames[i].isSelected;
        // }
      });

      this.updateFrames = function() {
        this.frames.arrFrames.sort(function(a, b){return a.order-b.order;});
        let count = 0;
        let newArrFrames = []
        for (let i = 0; i < this.frames.arrFrames.length; i++) {
          let frame = this.frames.arrFrames[i];
          if (frame.order >= 0) {
            frame.order = count;
            count++;
            newArrFrames.push(frame);
          } else {
            console.log(`splice ${i}`);
          }
        }
        this.frames.arrFrames = newArrFrames;
        $http.post('set_frames_json.php', $.param(this.frames), {
          url: 'set_frames_json.php',
          method: 'POST',
          headers: {'Content-Type': 'application/x-www-form-urlencoded'}
        }).then(function(res) {
          console.log(res.data);
        }, function(res) {
          console.log(res.data);
        });
        location.reload(); 
      };

      this.onclickImg = function(frame) {
        if (this.isSelectingStartFrame) {
          this.startFrame = frame.order;
          this.endFrame = this.startFrame + 1;
        } else {
          this.endFrame = frame.order + 1;
        }
        this.isSelectingStartFrame = !this.isSelectingStartFrame;
        for (let i = 0; i < this.frames.arrFrames.length; i++) {
          if (i >= this.startFrame && i < this.endFrame) {
            this.frames.arrFrames[i].selected = true;
          } else {
            this.frames.arrFrames[i].selected = false;
          }
        }
        console.log(`selected ${this.startFrame} to ${this.endFrame}`);
      }

      $scope.onDragOverSpanGap = function(ev) {
        ev.preventDefault();
      }

      $scope.onDropSpanGap = function(ev, insertAfterFrame) {
        ev.preventDefault();
        let insertAfterPosition = insertAfterFrame.order;
        console.log(`insert after ${insertAfterPosition}`);
        // count how many frames are selected in total
        let count = 0;
        for (let i = 0; i < this.frames.arrFrames.length; i++) {
          let frame = this.frames.arrFrames[i];
          if (frame.selected) {
            count++;
          }
        }
        // count how many frames are selected before the insertion point
        let selectedBeforeInsertion = 0;
        for (let i = 0; i <= insertAfterPosition; i++) {
          let frame = this.frames.arrFrames[i];
          if (frame.selected) {
            selectedBeforeInsertion++;
          }
        }
        insertAfterPosition -= selectedBeforeInsertion;
        let selected_order = insertAfterPosition + 1;
        let unselected_order = 0;
        for (let i = 0; i < this.frames.arrFrames.length; i++) {
          if (unselected_order === insertAfterPosition + 1) {
            unselected_order += selected_count;
          }
          let frame = this.frames.arrFrames[i];
          if (frame.selected === true) {
            frame.order = selected_order;
            selected_order++;
          } else {
            frame.order = unselected_order;
            unselected_order++;
          }
        }
        this.updateFrames();
      }

      this.onKeyupImg = function(ev) {
        console.log(ev.key);
        if (ev.key === 'Delete') {
          for (let i = 0; i < this.frames.arrFrames.length; i++) {
            let frame = this.frames.arrFrames[i];
            if (frame.selected) {
              console.log(`deleting frame ${i}`);
              frame.order = -1;
            } 
          }
          this.updateFrames();
        }
      }
    }
  ]});
