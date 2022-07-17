import { Component, OnInit } from '@angular/core';
import { VideoRecipe } from '../video-editor/video-recipe';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Subject } from 'rxjs';
import { environment } from '../../environments/environment';

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
  remoteHost:string = environment.remoteHost;

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
    const host = 'http://localhost:8000';
    this.http.post(`${this.remoteHost}/video-recipes/run`, body, httpOptions).subscribe((resp: any) => {
      console.log('resp=', resp);
      this.generatedVideoLink = `${this.remoteHost}/${resp}`;
    });
  }
  startOver() {
    localStorage.clear();
    console.log('localStorage cleared!')
    this.eventsSubject.next();
  }
}
