package com.frozenlab.voting.api.responses

import com.google.gson.annotations.SerializedName

class NewAccessTokenResponse {

    @SerializedName("access_token")
    var accessToken: String = ""
}
