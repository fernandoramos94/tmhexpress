import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

import { IonicModule } from '@ionic/angular';

import { ChangeStatusPageRoutingModule } from './change-status-routing.module';

import { ChangeStatusPage } from './change-status.page';

@NgModule({
  imports: [
    CommonModule,
    FormsModule,
    IonicModule,
    ChangeStatusPageRoutingModule
  ],
  declarations: [ChangeStatusPage]
})
export class ChangeStatusPageModule {}
