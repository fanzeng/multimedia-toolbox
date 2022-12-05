import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Component, OnInit, Input, Output, EventEmitter } from '@angular/core';
import { Frame } from './frame';
import { VideoRecipe } from './video-recipe';
import { Subscription, Observable } from 'rxjs';
import { environment } from '../../environments/environment';

@Component({
  selector: 'multimedia-toolbox-video-editor',
  templateUrl: './video-editor.component.html',
  styleUrls: ['./video-editor.component.css']
})
export class VideoEditorComponent implements OnInit {
  videoRecipe!: VideoRecipe;
  startFrame!: number;
  endFrame!: number;
  isSelectingStartFrame: boolean = true;
  remoteHost:string = environment.remoteHost;

  private eventsSubscription!: Subscription;

  @Input() events!: Observable<void>;
  @Output() videoRecipeCreated = new EventEmitter<VideoRecipe>();

  constructor(private http: HttpClient) { }
  ngOnInit(): void {

    console.log(localStorage.getItem('videoRecipeId'));
    if (!localStorage.getItem('videoRecipeId')) {
      this.createVideoRecipe();
    }
    else {
      const videoRecipeId = localStorage.getItem('videoRecipeId');
      this.reload(videoRecipeId);
    }
    this.eventsSubscription = this.events.subscribe(() => this.createVideoRecipe());
  }
  
  createVideoRecipe() {
    const httpOptions = {
      headers: new HttpHeaders({ 'Content-Type': 'text/plain' })
    };
    const formData: FormData = new FormData();
    this.http.post(`${this.remoteHost}/video-recipes`, formData, httpOptions).subscribe((resp: any) => {
      console.log(resp);
      this.videoRecipe = {
        id: resp.id,
        frames: []
      };
      console.log('this.videoRecipe', this.videoRecipe);
      localStorage.setItem('videoRecipeId', this.videoRecipe.id.toString());
      this.onVideoRecipeCreated();
    });
  }
  
  onVideoRecipeCreated() {
    console.log('this.videoRe', this.videoRecipe)
    this.videoRecipeCreated.emit(this.videoRecipe);
  }

  getFrameSrcFilename(index: number, frame: Frame): string {
    return frame.srcFilename;
  }

  reload(videoRecipeId: any): any {
    console.log('reloading');

    this.http.get(`${this.remoteHost}/video-recipes/${videoRecipeId}`,  { withCredentials: true }).subscribe((resp: any) => {
      console.log('resp', resp);
      console.log('resp[0].frames', resp[0].frames);
      this.videoRecipe = resp[0];
      this.videoRecipe.frames = resp[0].frames.map((frame: any): Frame => ({
        id: frame.id,
        order: frame.order,
        selected: false,
        numRepetition: frame.num_repetition,
        srcFilename: `${this.remoteHost}/${frame.src_filename}`
      }));
      console.log(this.videoRecipe.frames)
      this.videoRecipeCreated.emit(this.videoRecipe);
    })
  }

  updateFrames(): void {
    console.log('this.videoRecipe=', this.videoRecipe)
    this.videoRecipe.frames.sort((a, b) => a.order - b.order);
    let count = 0;
    let newFrames = [];
    for (let i = 0; i < this.videoRecipe.frames.length; i++) {
      let frame = this.videoRecipe.frames[i];
      console.log('frame=', frame)
      if (frame.order >= 0) {
        frame.order = count;
        count++;
        newFrames.push(frame);
      } else {
        console.log(`splice ${i}`);
      }
    }
    console.log('newFrames=', newFrames)

    this.videoRecipe.frames = newFrames;
    console.log(this.videoRecipe)
    const httpOptions = {
      headers: new HttpHeaders({ 'Content-Type': 'application/json' })
    };
    this.http.put(`${this.remoteHost}/video-recipes/${this.videoRecipe.id}`, JSON.stringify(this.videoRecipe), httpOptions).subscribe((resp: any) => {
      // this.videoRecipe = resp;
      console.log('res=', resp)
      console.log('this.videoRecipe=', this.videoRecipe);
      this.reload(this.videoRecipe.id);
    })
  };

  onclickImg(frame: any): void {
    if (this.isSelectingStartFrame) {
      this.startFrame = frame.order;
      this.endFrame = this.startFrame + 1;
    } else {
      this.endFrame = frame.order + 1;
    }
    this.isSelectingStartFrame = !this.isSelectingStartFrame;
    for (let i = 0; i < this.videoRecipe.frames.length; i++) {
      if (i >= this.startFrame && i < this.endFrame) {
        this.videoRecipe.frames[i].selected = true;
      } else {
        this.videoRecipe.frames[i].selected = false;
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
    for (let i = 0; i < this.videoRecipe.frames.length; i++) {
      let frame = this.videoRecipe.frames[i];
      if (frame.selected) {
        count++;
      }
    }
    // count how many frames are selected before the insertion point
    let selectedBeforeInsertion = 0;
    for (let i = 0; i <= insertAfterPosition; i++) {
      let frame = this.videoRecipe.frames[i];
      if (frame.selected) {
        selectedBeforeInsertion++;
      }
    }
    insertAfterPosition -= selectedBeforeInsertion;
    let selected_order = insertAfterPosition + 1;
    let unselected_order = 0;
    for (let i = 0; i < this.videoRecipe.frames.length; i++) {
      if (unselected_order === insertAfterPosition + 1) {
        unselected_order += count;
      }
      let frame = this.videoRecipe.frames[i];
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
      for (let i = 0; i < this.videoRecipe.frames.length; i++) {
        let frame = this.videoRecipe.frames[i];
        if (frame.selected) {
          console.log(`deleting frame ${i}`);
          frame.order = -1;
        } 
      }
      this.updateFrames();
    }
  }
  ngOnDestroy() {
    this.eventsSubscription.unsubscribe();
  }

}
