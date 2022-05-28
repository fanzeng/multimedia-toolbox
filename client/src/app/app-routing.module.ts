import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { VideoCreatorComponent } from './video-creator/video-creator.component';

const routes: Routes = [
  { path: 'video-creator', component: VideoCreatorComponent }
];

@NgModule({
  imports: [RouterModule.forRoot(routes)],
  exports: [RouterModule]
})
export class AppRoutingModule { }
