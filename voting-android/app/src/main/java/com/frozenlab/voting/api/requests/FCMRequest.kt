package com.frozenlab.marketplace.api.requests

import com.google.gson.annotations.SerializedName

class FCMRequest {

    @SerializedName("new_token")
    var newToken: String? = null

    @SerializedName("old_token")
    var oldToken: String? = null

    // Application ID:
    // 1 - Employer
    // 2 - Client
    @SerializedName("app_id")
    val appId: Int = 1
}