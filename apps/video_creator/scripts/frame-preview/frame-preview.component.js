'use strict';

angular.
  module('framePreview').
  component('framePreview', {
    templateUrl: 'scripts/frame-preview/frame-preview.template.html',
    controller: ['$http', '$scope', function framePreviewController($http, $scope) {
      var self = this;
      var is_selecting_start_frame = true;
      var start_frame = - 1;
      var end_frame = -1;
      $http.get('get_frames_json.php').then(function(response) {
        self.frames = response.data;
        for (var i = 0; i < self.frames['array_frames'].length; i++) {
          self.frames['array_frames'][i]['selected'] = String(self.frames['array_frames'][i]['selected']) == "true";
        }
      });

      this.onclick_update_frames = function() {
        self.frames['array_frames'].sort(function(a, b){return a.order-b.order;});

        var count = 0;
        var new_array_frames = []
        for (var i = 0; i < self.frames['array_frames'].length; i++) {
          var frame = self.frames['array_frames'][i];
          if (frame.order >= 0) {
            frame.order = count;
            count++;
            new_array_frames.push(frame);
          } else {
            console.log("splice" + i);
          }
        }
        self.frames['array_frames'] = new_array_frames;
        var post_data = $.param(self.frames);
        $http.post('set_frames_json.php', post_data, {
          url: 'set_frames_json.php',
          method: "POST",
          headers: {'Content-Type': 'application/x-www-form-urlencoded'}
        }).then(function(response) {
          console.log(response.data);
        }, function(response) {
          console.log(response.data);
        });
        location.reload(); 
      };

      this.onclick_img = function(frame) {
        if (this.is_selecting_start_frame === true) {
          this.start_frame = frame.order;
          this.end_frame = this.start_frame + 1;
          this.is_selecting_start_frame = false;
        } else {
          this.end_frame = frame.order + 1;
          this.is_selecting_start_frame = true;
        }
        for (var i = 0; i < this.frames['array_frames'].length; i++) {
          if (i >= this.start_frame && i < this.end_frame) {
            this.frames['array_frames'][i].selected = true;
          } else {
            this.frames['array_frames'][i].selected = false;
          }
        }
        console.log("selected " + this.start_frame + " to " + this.end_frame);
      }

      $scope.ondragover_span_gap = function(ev) {
        ev.preventDefault();
      }

      $scope.ondrop_span_gap = function(ev, insert_after_frame) {
        ev.preventDefault();
        var insert_after_position = insert_after_frame.order;
        console.log("insert after " + insert_after_position);
        // count how many frames are selected in total
        var selected_count = 0;
        for (var i = 0; i < self.frames['array_frames'].length; i++) {
          var frame = self.frames['array_frames'][i];
          if (frame.selected === true) {
            selected_count++;
          }
        }
        // count how many frames are selected before the insertion point
        var selected_before_insertion = 0;
        for (var i = 0; i <= insert_after_position; i++) {
          var frame = self.frames['array_frames'][i];
          if (frame.selected === true) {
            selected_before_insertion++;
          }
        }
        insert_after_position -= selected_before_insertion;
        var selected_order = insert_after_position + 1;
        var unselected_order = 0;
        for (var i = 0; i < self.frames['array_frames'].length; i++) {
          if (unselected_order === insert_after_position + 1) {
            unselected_order += selected_count;
          }
          var frame = self.frames['array_frames'][i];
          if (frame.selected === true) {
            frame.order = selected_order;
            selected_order++;
          } else {
            frame.order = unselected_order;
            unselected_order++;
          }
        }
        self.onclick_update_frames();
      }

      this.onkeyup_img = function(ev) {
        console.log(ev.key);
        if (ev.key === "Delete") {
          for (var i = 0; i < self.frames['array_frames'].length; i++) {
            var frame = self.frames['array_frames'][i];
            if (frame.selected === true) {
              console.log("deleting" + i);
              frame.order = -1;
            } 
          }
          this.onclick_update_frames();
        }
      }
    }
  ]});
