import { Component, OnInit, AfterViewInit } from '@angular/core';
import { Router } from '@angular/router';
import { ModalController } from '@ionic/angular';
import { BehaviorSubject } from 'rxjs';
import { ChangeStatusPage } from '../modal/change-status/change-status.page';

declare const google: any;

@Component({
    selector: 'app-map',
    templateUrl: './map.page.html',
    styleUrls: ['./map.page.scss'],
})
export class MapPage implements OnInit, AfterViewInit {

    data: any;

    position: any;
    directionsRenderer: any;
    directionsService: any;
    route: any;

    constructor(private router: Router, private modalCtrl: ModalController) {
        this.data = this.router.getCurrentNavigation().extras.state;

        navigator.geolocation.getCurrentPosition((res) => {
            console.log(res);
            this.position = res;
        })
    }

    ngOnInit() {
    }

    map: any;

    ngAfterViewInit(): void {

        this.directionsService = new google.maps.DirectionsService();

        this.map = new google.maps.Map(document.getElementById("map") as HTMLElement, {
            center: { lat: 21.5572338, lng: -102.3331963 },
            zoom: 7,
            disableDefaultUI: true
        });


        this.directionsRenderer = new google.maps.DirectionsRenderer();
        this.directionsRenderer.setMap(this.map);

        setTimeout(() => {
            var request = {
                origin: new google.maps.LatLng(this.position.coords.latitude, this.position.coords.longitude),
                destination: new google.maps.LatLng(this.data.lat, this.data.long),
                travelMode: google.maps.TravelMode.DRIVING,
            };
            this.modalEvent();
            this.directionsService.route(request, (response: any, status: any) => {
                
                if (status == google.maps.DirectionsStatus.OK) {
                    this.route = response.routes[0];
                    this.directionsRenderer.setDirections(response);

                    
                }
            }, (error: any) => {
                console.log(error)
            });
        }, 500);

    }

    async modalEvent() {
        const mySubject = new BehaviorSubject({});
        const modal = await this.modalCtrl.create({
            component: ChangeStatusPage,
            breakpoints: [0, 0.1, 0.2, 0.3, 0.4, 0.5],
            initialBreakpoint: 0.3,
            componentProps: {
                mySubject
            }
        });
        await modal.present();
    }

}
