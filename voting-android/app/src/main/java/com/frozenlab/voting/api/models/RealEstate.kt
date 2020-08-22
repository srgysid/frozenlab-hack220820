package com.frozenlab.voting.api.models

import com.google.gson.annotations.SerializedName

class RealEstate {

    @SerializedName("id")
    var id: Int = -1

    @SerializedName("house_id")
    var houseId: Int = -1

    @SerializedName("name")
    var name: String = ""

    @SerializedName("type_owner_name")
    var ownerTypeName: String = ""

    @SerializedName("ownership")
    var ownershipCode: String = ""

    @SerializedName("percent_own")
    var ownershipRate: Float = 0f

    @SerializedName("real_estate_area")
    var realEstateArea: String? = null

    @SerializedName("real_estate_type_short_name")
    var realEstateTypeShortName: String = ""

    @SerializedName("real_estate_num")
    var realEstateNumber: String = ""

    @SerializedName("house_num")
    var houseNumber: String = ""

    @SerializedName("street_name")
    var streetName: String = ""

    @SerializedName("street_full")
    var streetFull: String = ""

    @SerializedName("city_name")
    var cityName: String = ""

    @SerializedName("city_full")
    var cityFull: String = ""

    override fun toString(): String {
        return "${cityFull}, ${streetFull}, д. ${houseNumber}, ${realEstateTypeShortName} ${realEstateNumber}"
    }
}