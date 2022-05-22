import { Component, OnInit } from '@angular/core';
import { VideoRecipe } from '../video-editor/video-recipe';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Subject } from 'rxjs';
@Component({
  selector: 'app-video-creator',
  templateUrl: './video-creator.component.html',
  styleUrls: ['./video-creator.component.css']
})
export class VideoCreatorComponent implements OnInit {
  videoRecipeId: number = 0;
  videoRecipeUpdated: boolean = false;
  generatedVideoLink: string = '';
  videoWidth: number = 640;
  videoHeight: number = 480;
  framesPerSecond: number = 24;
  qualityRatio: number = 23;
  eventsSubject: Subject<void> = new Subject<void>();

  constructor(private http: HttpClient) { }

  ngOnInit(): void {
  }
  onVideoRecipeCreated(eventData: VideoRecipe):void {
    this.videoRecipeId = eventData.id;
  }
  onFileUploadSuccess(eventData: any, videoEditor: any):void {
    videoEditor.reload(this.videoRecipeId);
  }
  makeVideo(): void {
    const httpOptions = {
      headers: new HttpHeaders({ 'Content-Type': 'application/json' }),
      'responseType': 'text' as 'json'
    };
    let body = {
      videoRecipeId: this.videoRecipeId,
      videoWidth: this.videoWidth,
      videoHeight: this.videoHeight,
      framesPerSecond: this.framesPerSecond,
      qualityRatio: this.qualityRatio
    };
    this.http.post('http://localhost:8000/video-recipes/run', body, httpOptions).subscribe((resp: any) => {
      console.log('resp=', resp);
      this.generatedVideoLink = `http://localhost:8000/${resp}`;
    });
  }
  startOver() {
    localStorage.clear();
    console.log('localStorage cleared!')
    this.eventsSubject.next();
  }
}
