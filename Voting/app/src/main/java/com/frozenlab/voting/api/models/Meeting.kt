package com.frozenlab.voting.api.models

import com.frozenlab.extensions.asDateOrNull
import com.frozenlab.extensions.asIntOrNull
import com.frozenlab.extensions.asStringOrNull
import com.frozenlab.voting.Preferences
import com.google.gson.JsonDeserializationContext
import com.google.gson.JsonDeserializer
import com.google.gson.JsonElement
import java.lang.reflect.Type
import java.util.*

class Meeting {
    var id: Int = -1
    var registrationNumber: String = ""
    var createdAt:          Date?  = null
    var typeName:           String = ""
    var formName:           String = ""
    var startedAt:          Date?  = null
    var finishedAt:         Date?  = null
    var houseId:            Int    = -1
    var houseNumber:        String = ""
    var streetName:         String = ""
    var streetFull:         String = ""
    var cityName:           String = ""

    class Deserializer: JsonDeserializer<Meeting> {
        override fun deserialize(json: JsonElement?, typeOfT: Type?, context: JsonDeserializationContext?): Meeting {

            val voting = Meeting()
            val jsonObject = json?.asJsonObject ?: return voting

            voting.id                 = jsonObject.get("id")?.asIntOrNull() ?: -1
            voting.registrationNumber = jsonObject.get("reg_num")?.asStringOrNull() ?: ""
            voting.createdAt          = jsonObject.get("createdAt")?.asDateOrNull(Preferences.jsonDateFormat)
            voting.startedAt          = jsonObject.get("distant_started_at")?.asDateOrNull(Preferences.jsonDateFormat)
            voting.finishedAt         = jsonObject.get("finished_at")?.asDateOrNull(Preferences.jsonDateFormat)
            voting.typeName           = jsonObject.get("type_voting_name")?.asStringOrNull() ?: ""
            voting.formName           = jsonObject.get("form_voting_name")?.asStringOrNull() ?: ""
            voting.houseId            = jsonObject.get("house_id")?.asIntOrNull() ?: -1
            voting.houseNumber        = jsonObject.get("house_num")?.asStringOrNull() ?: ""
            voting.streetName         = jsonObject.get("street_name")?.asStringOrNull() ?: ""
            voting.streetFull         = jsonObject.get("street_full")?.asStringOrNull() ?: ""
            voting.cityName           = jsonObject.get("city_name")?.asStringOrNull() ?: ""

            return voting
        }

    }
}