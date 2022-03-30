import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { ChangeStatusPage } from './change-status.page';

const routes: Routes = [
  {
    path: '',
    component: ChangeStatusPage
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class ChangeStatusPageRoutingModule {}
