package com.frozenlab.voting.api.models

import com.google.gson.annotations.SerializedName

class Question {

    @SerializedName("id")
    var id: Int = -1

    @SerializedName("order_num")
    var order: Int = 0

    @SerializedName("title_short_name")
    var titleShort: String = ""

    @SerializedName("question_short_name")
    var questionShort: String = ""

    @SerializedName("topic")
    var topic: String = ""

    @SerializedName("proposal")
    var proposal: String = ""
}