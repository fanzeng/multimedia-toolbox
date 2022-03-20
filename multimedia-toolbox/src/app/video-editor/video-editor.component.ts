import { HttpClient } from '@angular/common/http';
import { Component, OnInit } from '@angular/core';
import { Frame } from './frame';
import { Frames } from './frames';

@Component({
  selector: 'app-video-editor',
  templateUrl: './video-editor.component.html',
  styleUrls: ['./video-editor.component.css']
})
export class VideoEditorComponent implements OnInit {
  frames!: Frames;
  startFrame!: number;
  endFrame!: number;
  isSelectingStartFrame: boolean = true;
  constructor(private http: HttpClient) { }

  ngOnInit(): void {
    this.http.get('http://localhost:8201/apps/video_creator/get_frames_json.php').subscribe(result => {
      console.log('frames_json=', result)
    })
  }

  getFrameSrcFilename(index: number, frame: Frame): string {
    return frame.srcFilename;
  }

  updateFrames(): void {
    this.frames.arrFrames.sort(function(a, b){return a.order-b.order;});
    let count = 0;
    let newArrFrames = [];
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
    // $http.post('set_frames_json.php', $.param(this.frames), {
    //   url: 'set_frames_json.php',
    //   method: 'POST',
    //   headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    // }).then(function(res) {
    //   console.log(res.data);
    // }, function(res) {
    //   console.log(res.data);
    // });
    // location.reload(); 
  };

  onclickImg(frame: any): void {
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

  onDragOverSpanGap(ev: any): void {
    ev.preventDefault();
  }

  onDropSpanGap(ev:any, insertAfterFrame: any): void {
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
        unselected_order += count;
      }
      let frame = this.frames.arrFrames[i];
      if (frame.selected) {
        frame.order = selected_order;
        selected_order++;
      } else {
        frame.order = unselected_order;
        unselected_order++;
      }
    }
    this.updateFrames();
  }

  onKeyupImg(ev: any): void {
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
