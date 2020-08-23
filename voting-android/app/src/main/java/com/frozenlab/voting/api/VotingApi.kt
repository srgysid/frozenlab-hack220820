package com.frozenlab.voting.api

import com.frozenlab.marketplace.api.requests.FCMRequest
import com.frozenlab.marketplace.api.requests.LoginRequest
import com.frozenlab.voting.api.models.*
import com.frozenlab.voting.api.responses.NewAccessTokenResponse
import io.reactivex.rxjava3.core.Completable
import io.reactivex.rxjava3.core.Single
import retrofit2.http.Body
import retrofit2.http.GET
import retrofit2.http.POST
import retrofit2.http.Query

interface VotingApi {

    // Login
    @POST("/v1/login")
    fun login(@Body request: LoginRequest): Single<NewAccessTokenResponse>

    // User

    @POST("/v1/user/fcm")
    fun sendFCMToken(@Body request: FCMRequest): Completable

    @GET("/v1/user/profile")
    fun getUserProfile(): Single<UserProfile>

    // Meeting

    @GET("/v1/meeting")
    fun getMeetings(): Single<ArrayList<Meeting>>

    @GET("/v1/meeting/view")
    fun getMeetings(@Query("house_id") houseId: Int): Single<ArrayList<Meeting>>

    @GET("/v1/meeting-question")
    fun getQuestions(@Query("meeting_id") meetingId: Int): Single<ArrayList<Question>>

    @POST("/v1/meeting-voter")
    fun sendAnswers(@Query("meeting_id") meetingId: Int, @Body answers: ArrayList<Answer>): Completable

    // Owner
    @GET("/v1/owner")
    fun getRealEstates(): Single<ArrayList<RealEstate>>
}