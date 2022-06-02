<?php

use Illuminate\Support\Facades\Route;


Route::group(['namespace' => 'Api\V1', 'prefix' => 'V1'], function () {

    Route::post('login', 'GuestController@login');
    Route::post('check_social_ability', 'GuestController@check_social_ability');
    Route::post('social_register', 'GuestController@social_register');
    Route::post('signup', 'GuestController@signup');
    Route::get('content/{type}', 'GuestController@content');
    Route::post('forgot_password', 'GuestController@forgot_password');
    Route::post('check_ability', 'GuestController@check_ability');
    Route::post('version_checker', 'GuestController@version_checker');
    //Airport List Api
    Route::post('getAirportList', 'GuestController@getAirportList');
    Route::post('upload_airport', 'GuestController@upload_airport');
    //TagList
    Route::post('getTagsList', 'GuestController@getTagsList');
    Route::post('createNotePDFtest', 'GuestController@createNotePDFtest');
    //Certificates list api
    Route::post('getCertificatesList', 'GuestController@getCertificatesList');


    Route::group(['middleware' => 'ApiTokenChecker'], function () {
        Route::group(['prefix' => 'user'], function () {
            Route::get('getProfile', 'UserController@getProfile');
            Route::post('edit_profile', 'UserController@edit_profile');
            Route::post('updatePassword', 'UserController@updatePassword');
            Route::post('switchRole', 'UserController@switchRole');
            Route::post('getUserProfile', 'UserController@getUserProfile');
            Route::get('logout', 'UserController@logout');
            Route::post('myEventsList', 'UserController@myEventsList');
            Route::post('eventDetail', 'UserController@eventDetail');
            Route::post('deleteEvent', 'UserController@deleteEvent');
            Route::post('home', 'UserController@home');
            Route::post('createNotePDF', 'UserController@createNotePDF');
        });

        Route::group(['prefix' => 'instructor'], function () {
            Route::post('allStudentsList', 'InstructorController@allStudentsList');
            Route::post('addStudent', 'InstructorController@addStudent');
            Route::post('removeStudent', 'InstructorController@removeStudent');
            Route::post('myStudentsList', 'InstructorController@myStudentsList');
            Route::post('addStudentNote', 'InstructorController@addStudentNote');
            Route::post('deleteStudentNote', 'InstructorController@deleteStudentNote');
            Route::post('studentNotes', 'InstructorController@studentNotes');
            Route::post('getNoteDetails', 'InstructorController@getNoteDetails');
            //while add name and email manually than this api will be call
            Route::post('addStudentViaEmail', 'InstructorController@addStudentViaEmail');
        });

        Route::group(['prefix' => 'student'], function () {
            Route::post('allInstructorList', 'StudentController@allInstructorList');
            Route::post('myInstructorsList', 'StudentController@myInstructorsList');
            Route::post('myNotes', 'StudentController@myNotes');
            Route::post('myInstructorNotes', 'StudentController@myInstructorNotes');
        });
        Route::group(['prefix' => 'event'], function () {
            Route::post('addEditEvent', 'EventController@addEditEvent');
        });
    });
});


